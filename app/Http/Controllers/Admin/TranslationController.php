<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameTranslation;
use App\Services\TranslationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TranslationController extends Controller
{
    protected $translationService;

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    /**
     * Exibe o painel de administração de traduções
     */
    public function index()
    {
        // Estatísticas gerais
        $totalGames = Game::count();
        $translatedGames = Game::whereHas('translations', function($q) {
            $q->where('field_name', 'description');
        })->count();
        $untranslatedGames = $totalGames - $translatedGames;
        
        // Jogos recém-adicionados sem tradução (últimos 7 dias)
        $newUntranslatedGames = Game::where('created_at', '>=', now()->subDays(7))
            ->whereDoesntHave('translations', function($q) {
                $q->where('field_name', 'description');
            })
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Traduções recentes
        $recentTranslations = GameTranslation::with(['game' => function($query) {
                $query->select('id', 'title');
            }])
            ->where('field_name', 'description')
            ->whereHas('game') // Só pega traduções com jogos válidos
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Estatísticas por método de tradução
        $translationStats = GameTranslation::where('field_name', 'description')
            ->selectRaw('translation_method, COUNT(*) as count, AVG(confidence_score) as avg_confidence')
            ->groupBy('translation_method')
            ->get();

        return view('admin.translations.index', compact(
            'totalGames',
            'translatedGames', 
            'untranslatedGames',
            'newUntranslatedGames',
            'recentTranslations',
            'translationStats'
        ));
    }

    /**
     * Executa tradução em lote via AJAX - Versão assíncrona otimizada
     */
    public function translateBatch(Request $request)
    {
        $request->validate([
            'limit' => 'nullable|integer|min:1|max:1000',
            'force' => 'nullable|boolean'
        ]);

        $limit = $request->input('limit', 50);
        $force = $request->input('force', false);

        try {
            // Para lotes grandes (>100), usar sistema de Jobs assíncrono
            if ($limit > 100) {
                return $this->startAsyncTranslation($limit, $force);
            }

            // Para lotes pequenos, execução síncrona rápida com timeout reduzido
            set_time_limit(60); // Apenas 1 minuto para lotes pequenos

            // Buscar jogos para traduzir
            $query = Game::whereDoesntHave('translations', function($q) {
                $q->where('field_name', 'description');
            });

            if ($force) {
                $query = Game::query(); // Se force=true, pega todos os jogos
            }

            $games = $query->limit($limit)->get();
            
            $stats = [
                'processed' => 0,
                'translated' => 0,
                'ignored' => 0,
                'errors' => 0,
                'translations' => []
            ];

            $rawgService = app(\App\Services\RawgService::class);

            foreach ($games as $game) {
                try {
                    $stats['processed']++;
                    
                    // Lógica simplificada e otimizada para descrição
                    $descriptionToTranslate = $this->getOptimizedGameDescription($game, $rawgService, $force);
                    
                    if (!$descriptionToTranslate) {
                        $stats['ignored']++;
                        continue;
                    }
                    
                    // Verificar se está em inglês
                    if (!$this->translationService->isEnglishText($descriptionToTranslate)) {
                        $stats['ignored']++;
                        continue;
                    }
                    
                    $translation = $this->translationService->translateGameDescription($game, $force);
                    
                    if ($translation) {
                        $stats['translated']++;
                        $stats['translations'][] = [
                            'game_id' => $game->id,
                            'game_title' => $game->title,
                            'translation_id' => $translation->id,
                            'method' => $translation->translation_method,
                            'confidence' => $translation->confidence_score
                        ];
                    } else {
                        $stats['ignored']++;
                    }
                } catch (\Exception $e) {
                    $stats['errors']++;
                    Log::error('Erro ao traduzir jogo ID ' . $game->id . ': ' . $e->getMessage());
                }

                // Micro pausa para evitar sobrecarga
                usleep(100000); // 0.1 segundos
            }

            return response()->json([
                'success' => true,
                'message' => "Tradução concluída! {$stats['translated']} de {$stats['processed']} jogos traduzidos.",
                'stats' => [
                    'translated' => $stats['translated'],
                    'ignored' => $stats['ignored'],
                    'errors' => $stats['errors'],
                    'total' => $stats['processed'],
                    'processed' => $stats['processed']
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erro na tradução em lote: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao executar tradução: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Inicia tradução assíncrona usando Jobs
     */
    private function startAsyncTranslation(int $limit, bool $force): \Illuminate\Http\JsonResponse
    {
        try {
            // Buscar IDs dos jogos para traduzir
            $query = Game::whereDoesntHave('translations', function($q) {
                $q->where('field_name', 'description');
            });

            if ($force) {
                $query = Game::query();
            }

            $gameIds = $query->limit($limit)->pluck('id')->toArray();
            
            if (empty($gameIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum jogo encontrado para traduzir.'
                ]);
            }

            // Dividir em chunks de 20 jogos para processamento paralelo
            $chunks = array_chunk($gameIds, 20);
            $batchId = uniqid('async_');
            
            // Inicializar status no cache
            Cache::put("translation_batch_{$batchId}", [
                'status' => 'queued',
                'total_chunks' => count($chunks),
                'chunks_completed' => 0,
                'stats' => ['processed' => 0, 'translated' => 0, 'ignored' => 0, 'errors' => 0],
                'started_at' => now()->toISOString()
            ], now()->addHours(24));

            // Disparar Jobs
            foreach ($chunks as $index => $chunk) {
                \App\Jobs\TranslateGamesBatchJob::dispatch($chunk, $force, $batchId . "_chunk_{$index}");
            }

            return response()->json([
                'success' => true,
                'async' => true,
                'batch_id' => $batchId,
                'message' => "Tradução assíncrona iniciada! {$limit} jogos divididos em " . count($chunks) . " lotes.",
                'total_games' => count($gameIds),
                'total_chunks' => count($chunks)
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao iniciar tradução assíncrona: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao iniciar tradução assíncrona: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Busca descrição otimizada (prioritiza banco, evita APIs desnecessárias)
     */
    private function getOptimizedGameDescription(Game $game, $rawgService, bool $force): ?string
    {
        // Primeiro, verificar descrição no banco
        if (!empty($game->description) && 
            !str_contains(strtolower($game->description), 'oferta encontrada') &&
            strlen($game->description) > 50) {
            return $game->description;
        }

        // Se não tem descrição válida e não é força, não buscar APIs (para performance)
        if (!$force) {
            return null;
        }

        // Apenas se for força, buscar do RAWG (com timeout reduzido)
        try {
            $rawgData = $rawgService->getGameDetails($game->title);
            
            if (isset($rawgData['description_raw']) && !empty($rawgData['description_raw'])) {
                return strip_tags($rawgData['description_raw']);
            }
        } catch (\Exception $e) {
            Log::warning("Erro ao buscar descrição para {$game->title}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Traduz um jogo específico
     */
    public function translateSingle(Request $request)
    {
        $request->validate([
            'game_id' => 'required|integer|exists:games,id',
            'force' => 'nullable|boolean'
        ]);

        $gameId = $request->input('game_id');
        $force = $request->input('force', false);
        $game = Game::findOrFail($gameId);

        try {
            $translation = $this->translationService->translateGameDescription($game, $force);
            
            if ($translation) {
                return response()->json([
                    'success' => true,
                    'message' => 'Jogo traduzido com sucesso!',
                    'translation' => [
                        'id' => $translation->id,
                        'translated_text' => $translation->translated_text,
                        'method' => $translation->translation_method,
                        'confidence' => $translation->confidence_score,
                        'created_at' => $translation->created_at->format('d/m/Y H:i')
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível traduzir o jogo. Verifique se há descrição disponível.'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Erro ao traduzir jogo ID ' . $game->id . ': ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao traduzir: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove uma tradução
     */
    public function deleteTranslation(GameTranslation $translation)
    {
        try {
            $gameTitle = $translation->game->title;
            $translation->delete();

            return response()->json([
                'success' => true,
                'message' => "Tradução do jogo '{$gameTitle}' removida com sucesso!"
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao deletar tradução ID ' . $translation->id . ': ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover tradução: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detecta automaticamente novos jogos sem tradução
     */
    public function detectNewGames()
    {
        // Jogos adicionados nas últimas 24 horas sem tradução
        $newGames = Game::where('created_at', '>=', now()->subDay())
            ->whereDoesntHave('translations', function($q) {
                $q->where('field_name', 'description');
            })
            ->with(['translations'])
            ->orderBy('created_at', 'desc')
            ->get();

        $gamesNeedingTranslation = [];

        foreach ($newGames as $game) {
            // Verifica se a descrição não está vazia e contém texto em inglês
            if (!empty($game->description) && $this->containsEnglishText($game->description)) {
                $gamesNeedingTranslation[] = [
                    'id' => $game->id,
                    'title' => $game->title,
                    'description' => substr($game->description, 0, 100) . '...',
                    'created_at' => $game->created_at->format('d/m/Y H:i'),
                    'store' => $game->store_name
                ];
            }
        }

        return response()->json([
            'success' => true,
            'count' => count($gamesNeedingTranslation),
            'games' => $gamesNeedingTranslation
        ]);
    }

    /**
     * Método auxiliar para detectar texto em inglês
     */
    private function containsEnglishText($text)
    {
        // Palavras comuns em inglês que indicam que o texto precisa de tradução
        $englishWords = [
            'the', 'and', 'you', 'that', 'was', 'for', 'are', 'with', 'his', 'they',
            'this', 'have', 'from', 'not', 'been', 'has', 'her', 'more', 'can',
            'said', 'each', 'which', 'them', 'into', 'time', 'will', 'about',
            'game', 'play', 'player', 'world', 'adventure', 'action', 'story',
            'character', 'battle', 'hero', 'journey', 'quest', 'epic', 'fight'
        ];

        $text = strtolower($text);
        $wordCount = 0;

        foreach ($englishWords as $word) {
            if (strpos($text, ' ' . $word . ' ') !== false || 
                strpos($text, $word . ' ') === 0 || 
                strpos($text, ' ' . $word) === strlen($text) - strlen($word) - 1) {
                $wordCount++;
            }
        }

        // Se encontrar 3 ou mais palavras em inglês, considera que precisa tradução
        return $wordCount >= 3;
    }

    /**
     * Executa comando Artisan via web interface
     */
    public function runArtisanCommand(Request $request)
    {
        $request->validate([
            'command' => 'required|string|in:translate-all,translate-new',
            'limit' => 'nullable|integer|min:1|max:1000'
        ]);

        try {
            $command = $request->input('command');
            $limit = $request->input('limit', 100);

            if ($command === 'translate-all') {
                Artisan::call('games:translate', ['--limit' => $limit]);
            } else {
                // Para traduzir apenas jogos novos (últimos 7 dias)
                Artisan::call('games:translate', [
                    '--limit' => $limit,
                    '--recent' => true
                ]);
            }

            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'Comando executado com sucesso!',
                'output' => $output
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao executar comando Artisan: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao executar comando: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Traduz todos os jogos sem tradução (otimizado para evitar timeouts)
     */
    public function translateAll()
    {
        // Timeout reduzido para 60 segundos
        set_time_limit(60);
        
        try {
            // Reduzir limite para 30 jogos por vez para evitar timeout
            $limit = 30;
            
            // Buscar jogos sem tradução (apenas com descrições válidas no banco)
            $untranslatedGames = Game::whereDoesntHave('translations', function($query) {
                $query->where('field_name', 'description');
            })
            ->where(function($query) {
                $query->whereNotNull('description')
                      ->where('description', '!=', '')
                      ->whereRaw('LENGTH(description) > 50')
                      ->whereRaw('LOWER(description) NOT LIKE ?', ['%oferta encontrada%']);
            })
            ->limit($limit)
            ->get();

            Log::info("TranslateAll: Processando {$untranslatedGames->count()} jogos (limite: {$limit}) - apenas com descrições válidas");

            $translated = 0;
            $errors = 0;
            $skipped = 0;

            foreach ($untranslatedGames as $index => $game) {
                try {
                    Log::info("TranslateAll: [{$index}/{$untranslatedGames->count()}] Processando jogo ID {$game->id} - {$game->title}");
                    
                    // Usar descrição do banco (já validada na query)
                    $descriptionToTranslate = $game->description;
                    Log::info("TranslateAll: Usando descrição do banco: " . substr($descriptionToTranslate, 0, 100) . "...");
                    
                    // Verificar se a descrição está em inglês
                    $isEnglish = $this->translationService->isEnglishText($descriptionToTranslate);
                    Log::info("TranslateAll: Texto detectado como inglês: " . ($isEnglish ? 'SIM' : 'NÃO'));
                    
                    if ($isEnglish) {
                        $result = $this->translationService->translateText($descriptionToTranslate);
                        
                        if ($result['success']) {
                            // Salvar tradução
                            GameTranslation::create([
                                'game_id' => $game->id,
                                'field_name' => 'description',
                                'original_text' => $descriptionToTranslate,
                                'translated_text' => $result['translation'],
                                'translation_method' => $result['method'],
                                'confidence_score' => $result['confidence'],
                                'translated_at' => now()
                            ]);
                            
                            $translated++;
                            Log::info("TranslateAll: Jogo ID {$game->id} traduzido com sucesso");
                        } else {
                            $errors++;
                            Log::warning("TranslateAll: Falha ao traduzir jogo ID {$game->id}: " . ($result['error'] ?? 'Erro desconhecido'));
                        }
                    } else {
                        $skipped++;
                        Log::info("TranslateAll: Jogo ID {$game->id} ignorado (não está em inglês)");
                    }
                } catch (\Exception $e) {
                    $errors++;
                    Log::error("TranslateAll: Erro ao processar jogo ID {$game->id}: " . $e->getMessage());
                }
                
                // Micro pausa para não sobrecarregar o sistema
                usleep(100000); // 0.1 segundos
            }

            // Verificar se há mais jogos que precisam buscar do RAWG
            $remainingWithoutDesc = Game::whereDoesntHave('translations', function($query) {
                $query->where('field_name', 'description');
            })
            ->where(function($query) {
                $query->whereNull('description')
                      ->orWhere('description', '=', '')
                      ->orWhereRaw('LENGTH(description) <= 50')
                      ->orWhereRaw('LOWER(description) LIKE ?', ['%oferta encontrada%']);
            })
            ->count();

            $message = "Tradução concluída! {$translated} jogos traduzidos, {$skipped} ignorados, {$errors} erros. ";
            $message .= "(Processados: {$untranslatedGames->count()}/{$limit}). ";
            
            if ($remainingWithoutDesc > 0) {
                $message .= "Há {$remainingWithoutDesc} jogos que precisam buscar descrições do RAWG (use 'Traduzir em Lote' com limite maior para processamento assíncrono).";
            }
            
            Log::info("TranslateAll: " . $message);

            return response()->json([
                'success' => true,
                'message' => $message,
                'stats' => [
                    'translated' => $translated,
                    'ignored' => $skipped,
                    'errors' => $errors,
                    'total' => $untranslatedGames->count(),
                    'processed' => $untranslatedGames->count(),
                    'remaining_without_desc' => $remainingWithoutDesc
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('TranslateAll: Erro geral: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Monitora progresso de tradução assíncrona
     */
    public function checkBatchProgress(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|string'
        ]);

        $batchId = $request->input('batch_id');
        
        try {
            $status = Cache::get("translation_batch_{$batchId}");
            
            if (!$status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batch não encontrado ou expirado.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'batch_id' => $batchId,
                'status' => $status
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao verificar progresso do batch: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar progresso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lista todos os batches ativos
     */
    public function listActiveBatches()
    {
        try {
            // Em um cenário real, você poderia usar Redis para listar todas as chaves
            // Por simplicidade, vamos retornar apenas informações sobre jobs em execução
            
            $queueSize = DB::table('jobs')->count();
            $failedJobs = DB::table('failed_jobs')->count();
            
            return response()->json([
                'success' => true,
                'queue_size' => $queueSize,
                'failed_jobs' => $failedJobs,
                'message' => $queueSize > 0 ? 
                    "Há {$queueSize} jobs na fila de processamento." : 
                    "Nenhum job em execução no momento."
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao listar batches ativos: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar batches: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Inicia processo de atualização de descrições de todos os jogos
     */
    public function updateAllDescriptions(Request $request)
    {
        $request->validate([
            'limit' => 'nullable|integer|min:1|max:5000',
            'force' => 'nullable|boolean'
        ]);

        $limit = $request->input('limit', 1000);
        $force = $request->input('force', true);

        try {
            // Buscar jogos que precisam de atualização
            $query = Game::query();
            
            if (!$force) {
                // Se não for força, pegar apenas jogos sem descrição ou com descrição ruim
                $query->where(function($q) {
                    $q->whereNull('description')
                      ->orWhere('description', '=', '')
                      ->orWhereRaw('LENGTH(description) < 100')
                      ->orWhereRaw('LOWER(description) LIKE ?', ['%oferta encontrada%'])
                      ->orWhereRaw('LOWER(description) LIKE ?', ['%em promoção%'])
                      ->orWhereRaw('LOWER(description) LIKE ?', ['%desconto%']);
                });
            }

            $gameIds = $query->limit($limit)->pluck('id')->toArray();
            
            if (empty($gameIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum jogo encontrado para atualizar descrições.'
                ]);
            }

            // Dividir em chunks de 15 jogos (menor que tradução por causa das APIs)
            $chunks = array_chunk($gameIds, 15);
            $batchId = uniqid('desc_update_');
            
            // Inicializar status no cache
            Cache::put("description_update_{$batchId}", [
                'status' => 'queued',
                'total_chunks' => count($chunks),
                'chunks_completed' => 0,
                'stats' => ['processed' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => 0],
                'started_at' => now()->toISOString()
            ], now()->addHours(24));

            // Disparar Jobs
            foreach ($chunks as $index => $chunk) {
                \App\Jobs\UpdateGameDescriptionsJob::dispatch($chunk, $batchId . "_chunk_{$index}", $force);
            }

            Log::info("UpdateDescriptions: Iniciado batch {$batchId} com {$limit} jogos em " . count($chunks) . " chunks");

            return response()->json([
                'success' => true,
                'batch_id' => $batchId,
                'message' => "Atualização de descrições iniciada! {$limit} jogos divididos em " . count($chunks) . " lotes.",
                'total_games' => count($gameIds),
                'total_chunks' => count($chunks),
                'estimated_time' => round((count($gameIds) * 0.75) / 60, 1) . ' minutos'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao iniciar atualização de descrições: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao iniciar atualização: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Monitora progresso da atualização de descrições
     */
    public function checkDescriptionUpdateProgress(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|string'
        ]);

        $batchId = $request->input('batch_id');
        
        try {
            $status = Cache::get("description_update_{$batchId}");
            
            if (!$status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batch de atualização não encontrado ou expirado.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'batch_id' => $batchId,
                'status' => $status
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao verificar progresso da atualização: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar progresso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Monitora progresso das correções de traduções em tempo real
     */
    public function getTranslationProgress()
    {
        try {
            $totalGames = Game::whereNotNull('description')
                ->whereRaw('LENGTH(description) > 300')
                ->count();
            
            $totalTranslations = GameTranslation::where('field_name', 'description')
                ->whereHas('game', function($q) {
                    $q->whereNotNull('description')
                      ->whereRaw('LENGTH(description) > 300');
                })
                ->count();

            // Estatísticas de qualidade
            $excellentTranslations = GameTranslation::with('game')
                ->where('field_name', 'description')
                ->whereHas('game', function($q) {
                    $q->whereNotNull('description')
                      ->whereRaw('LENGTH(description) > 300');
                })
                ->get()
                ->filter(function($translation) {
                    if (!$translation->game || !$translation->game->description) return false;
                    $ratio = strlen($translation->translated_text) / strlen($translation->game->description);
                    return $ratio >= 0.7;
                })
                ->count();

            $goodTranslations = GameTranslation::with('game')
                ->where('field_name', 'description')
                ->whereHas('game', function($q) {
                    $q->whereNotNull('description')
                      ->whereRaw('LENGTH(description) > 300');
                })
                ->get()
                ->filter(function($translation) {
                    if (!$translation->game || !$translation->game->description) return false;
                    $ratio = strlen($translation->translated_text) / strlen($translation->game->description);
                    return $ratio >= 0.4 && $ratio < 0.7;
                })
                ->count();

            $poorTranslations = $totalTranslations - $excellentTranslations - $goodTranslations;

            // Atividade recente
            $recentUpdates = GameTranslation::where('field_name', 'description')
                ->where('updated_at', '>=', now()->subHours(2))
                ->count();

            $veryRecentUpdates = GameTranslation::where('field_name', 'description')
                ->where('updated_at', '>=', now()->subMinutes(30))
                ->count();

            // Jobs na fila
            $queueSize = DB::table('jobs')->count();
            $failedJobs = DB::table('failed_jobs')->count();

            // Últimas correções de qualidade
            $recentQualityTranslations = GameTranslation::with('game')
                ->where('field_name', 'description')
                ->where('updated_at', '>=', now()->subMinutes(10))
                ->whereHas('game', function($q) {
                    $q->whereNotNull('description');
                })
                ->get()
                ->filter(function($translation) {
                    if (!$translation->game) return false;
                    $ratio = strlen($translation->translated_text) / strlen($translation->game->description);
                    return $ratio >= 0.7;
                })
                ->map(function($translation) {
                    $ratio = round((strlen($translation->translated_text) / strlen($translation->game->description)) * 100, 1);
                    return [
                        'game_title' => $translation->game->title,
                        'ratio' => $ratio,
                        'updated_at' => $translation->updated_at->format('H:i:s')
                    ];
                })
                ->sortByDesc('ratio')
                ->take(5)
                ->values();

            return response()->json([
                'success' => true,
                'stats' => [
                    'total_eligible_games' => $totalGames,
                    'total_translations' => $totalTranslations,
                    'coverage_percentage' => round(($totalTranslations / max($totalGames, 1)) * 100, 1),
                    'excellent_translations' => $excellentTranslations,
                    'good_translations' => $goodTranslations,
                    'poor_translations' => $poorTranslations,
                    'excellent_percentage' => round(($excellentTranslations / max($totalTranslations, 1)) * 100, 1),
                    'good_percentage' => round(($goodTranslations / max($totalTranslations, 1)) * 100, 1),
                    'poor_percentage' => round(($poorTranslations / max($totalTranslations, 1)) * 100, 1),
                    'quality_score' => round((($excellentTranslations + $goodTranslations) / max($totalTranslations, 1)) * 100, 1)
                ],
                'activity' => [
                    'recent_updates_2h' => $recentUpdates,
                    'recent_updates_30m' => $veryRecentUpdates,
                    'queue_size' => $queueSize,
                    'failed_jobs' => $failedJobs,
                    'is_active' => $veryRecentUpdates > 0
                ],
                'recent_quality_translations' => $recentQualityTranslations,
                'timestamp' => now()->format('H:i:s')
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao obter progresso das traduções: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter progresso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verifica quantas traduções críticas ainda precisam ser corrigidas
     */
    public function getCriticalTranslationsStatus()
    {
        try {
            // Traduções extremamente ruins (menos de 5%)
            $criticalTranslations = GameTranslation::with('game')
                ->where('field_name', 'description')
                ->whereHas('game', function($q) {
                    $q->whereNotNull('description')
                      ->whereRaw('LENGTH(description) > 500');
                })
                ->get()
                ->filter(function($translation) {
                    if (!$translation->game || !$translation->game->description) return false;
                    $ratio = strlen($translation->translated_text) / strlen($translation->game->description);
                    return $ratio < 0.05;
                })
                ->count();

            // Traduções incompletas (menos de 40%)
            $incompleteTranslations = GameTranslation::with('game')
                ->where('field_name', 'description')
                ->whereHas('game', function($q) {
                    $q->whereNotNull('description')
                      ->whereRaw('LENGTH(description) > 300');
                })
                ->get()
                ->filter(function($translation) {
                    if (!$translation->game || !$translation->game->description) return false;
                    $ratio = strlen($translation->translated_text) / strlen($translation->game->description);
                    return $ratio < 0.4;
                })
                ->count();

            // Jobs relacionados a correções na fila
            $correctionJobs = DB::table('jobs')
                ->where('payload', 'like', '%corrected_%')
                ->orWhere('payload', 'like', '%critical_fix_%')
                ->count();

            return response()->json([
                'success' => true,
                'critical_status' => [
                    'critical_translations' => $criticalTranslations,
                    'incomplete_translations' => $incompleteTranslations,
                    'correction_jobs_in_queue' => $correctionJobs,
                    'estimated_completion' => $correctionJobs > 0 ? 
                        round(($correctionJobs * 10) / 60, 1) . ' minutos' : 
                        'Completo'
                ],
                'timestamp' => now()->format('H:i:s d/m/Y')
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao verificar status crítico: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estatísticas detalhadas sobre descrições dos jogos
     */
    public function getDescriptionStats()
    {
        try {
            $totalGames = Game::count();
            
            // Jogos sem descrição
            $withoutDescription = Game::where(function($q) {
                $q->whereNull('description')->orWhere('description', '=', '');
            })->count();
            
            // Jogos com descrição curta (< 100 chars)
            $shortDescription = Game::whereNotNull('description')
                ->where('description', '!=', '')
                ->whereRaw('LENGTH(description) < 100')
                ->count();
            
            // Jogos com descrição válida (>= 100 chars)
            $validDescription = Game::whereNotNull('description')
                ->where('description', '!=', '')
                ->whereRaw('LENGTH(description) >= 100')
                ->whereRaw('LOWER(description) NOT LIKE ?', ['%oferta encontrada%'])
                ->count();
            
            // Jogos com descrição genérica (oferta)
            $genericDescription = Game::whereNotNull('description')
                ->where('description', '!=', '')
                ->where(function($q) {
                    $q->whereRaw('LOWER(description) LIKE ?', ['%oferta encontrada%'])
                      ->orWhereRaw('LOWER(description) LIKE ?', ['%em promoção%'])
                      ->orWhereRaw('LOWER(description) LIKE ?', ['%desconto%']);
                })->count();

            // DLCs e Passes
            $dlcCount = Game::where(function($q) {
                $q->whereRaw('LOWER(title) LIKE ?', ['%dlc%'])
                  ->orWhereRaw('LOWER(title) LIKE ?', ['%expansion%'])
                  ->orWhereRaw('LOWER(title) LIKE ?', ['%season pass%'])
                  ->orWhereRaw('LOWER(title) LIKE ?', ['%passe%'])
                  ->orWhereRaw('LOWER(title) LIKE ?', ['%pack%']);
            })->count();

            return response()->json([
                'success' => true,
                'stats' => [
                    'total_games' => $totalGames,
                    'without_description' => $withoutDescription,
                    'short_description' => $shortDescription,
                    'valid_description' => $validDescription,
                    'generic_description' => $genericDescription,
                    'dlc_count' => $dlcCount,
                    'need_update' => $withoutDescription + $shortDescription + $genericDescription,
                    'coverage_percentage' => round(($validDescription / $totalGames) * 100, 1)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao obter estatísticas de descrições: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter estatísticas: ' . $e->getMessage()
            ], 500);
        }
    }
}