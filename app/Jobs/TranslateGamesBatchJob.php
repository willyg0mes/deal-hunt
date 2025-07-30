<?php

namespace App\Jobs;

use App\Models\Game;
use App\Models\GameTranslation;
use App\Services\RawgService;
use App\Services\TranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class TranslateGamesBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $gameIds;
    protected $force;
    protected $batchId;

    public $timeout = 300; // 5 minutos por job
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(array $gameIds, bool $force = false, string $batchId = null)
    {
        $this->gameIds = $gameIds;
        $this->force = $force;
        $this->batchId = $batchId ?? uniqid('batch_');
    }

    /**
     * Execute the job.
     */
    public function handle(TranslationService $translationService, RawgService $rawgService): void
    {
        Log::info("TranslateBatch Job {$this->batchId}: Iniciando com " . count($this->gameIds) . " jogos");
        
        $stats = [
            'processed' => 0,
            'translated' => 0,
            'ignored' => 0,
            'errors' => 0
        ];

        // Atualizar cache com status inicial
        $this->updateBatchStatus($stats, 'processing');

        foreach ($this->gameIds as $index => $gameId) {
            try {
                $game = Game::find($gameId);
                if (!$game) {
                    Log::warning("TranslateBatch Job {$this->batchId}: Jogo ID {$gameId} não encontrado");
                    continue;
                }

                $stats['processed']++;
                
                Log::info("TranslateBatch Job {$this->batchId}: [{$index}/" . count($this->gameIds) . "] Processando {$game->title}");

                // Verificar se já tem tradução e não é força
                if (!$this->force && $game->translations()->where('field_name', 'description')->exists()) {
                    $stats['ignored']++;
                    continue;
                }

                $descriptionToTranslate = $this->getGameDescription($game, $rawgService);
                
                if (!$descriptionToTranslate) {
                    Log::info("TranslateBatch Job {$this->batchId}: Jogo {$game->title} sem descrição válida");
                    $stats['ignored']++;
                    continue;
                }

                // Verificar se está em inglês
                if (!$translationService->isEnglishText($descriptionToTranslate)) {
                    Log::info("TranslateBatch Job {$this->batchId}: Jogo {$game->title} não está em inglês");
                    $stats['ignored']++;
                    continue;
                }

                // Traduzir
                $result = $translationService->translateText($descriptionToTranslate);
                
                if ($result['success']) {
                    // Usar updateOrCreate para evitar problemas de timing
                    $translation = GameTranslation::updateOrCreate(
                        [
                            'game_id' => $game->id,
                            'field_name' => 'description'
                        ],
                        [
                            'original_text' => $descriptionToTranslate,
                            'translated_text' => $result['translation'],
                            'translation_method' => $result['method'],
                            'confidence_score' => $result['confidence'],
                            'translated_at' => now()
                        ]
                    );
                    
                    // Log com tamanhos para debug
                    Log::info("TranslateBatch Job {$this->batchId}: Jogo {$game->title} traduzido com sucesso - " . 
                        strlen($descriptionToTranslate) . " chars original -> " . 
                        strlen($result['translation']) . " chars traduzido");
                    
                    $stats['translated']++;
                } else {
                    $stats['errors']++;
                    Log::warning("TranslateBatch Job {$this->batchId}: Falha ao traduzir {$game->title}: " . ($result['error'] ?? 'Erro desconhecido'));
                }

                // Atualizar progresso a cada 5 jogos
                if ($index % 5 === 0) {
                    $this->updateBatchStatus($stats, 'processing', $index + 1, count($this->gameIds));
                }

                // Pausa entre traduções para não sobrecarregar APIs
                usleep(500000); // 0.5 segundos

            } catch (\Exception $e) {
                $stats['errors']++;
                Log::error("TranslateBatch Job {$this->batchId}: Erro ao processar jogo ID {$gameId}: " . $e->getMessage());
            }
        }

        // Finalizar
        $this->updateBatchStatus($stats, 'completed');
        
        Log::info("TranslateBatch Job {$this->batchId}: Concluído - " . json_encode($stats));
    }

    /**
     * Buscar descrição do jogo com otimizações
     */
    private function getGameDescription(Game $game, RawgService $rawgService): ?string
    {
        // Primeiro, verificar descrição no banco
        if (!empty($game->description) && 
            !str_contains(strtolower($game->description), 'oferta encontrada') &&
            strlen($game->description) > 50) {
            return $game->description;
        }

        // Buscar do RAWG com timeout reduzido
        try {
            $rawgData = $rawgService->getGameDetails($game->title);
            
            if (isset($rawgData['description_raw']) && !empty($rawgData['description_raw'])) {
                return strip_tags($rawgData['description_raw']);
            }

            // Tentar fontes alternativas
            $alternativeDesc = $rawgService->getGameDescriptionFromAlternativeSources($game->title);
            if ($alternativeDesc && strlen($alternativeDesc) > 50) {
                return $alternativeDesc;
            }
        } catch (\Exception $e) {
            Log::warning("TranslateBatch Job {$this->batchId}: Erro ao buscar descrição para {$game->title}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Atualizar status do batch no cache
     */
    private function updateBatchStatus(array $stats, string $status, int $current = 0, int $total = 0): void
    {
        $data = [
            'status' => $status,
            'stats' => $stats,
            'current' => $current,
            'total' => $total ?: count($this->gameIds),
            'updated_at' => now()->toISOString()
        ];

        Cache::put("translation_batch_{$this->batchId}", $data, now()->addHours(24));
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("TranslateBatch Job {$this->batchId} falhou: " . $exception->getMessage());
        
        $stats = [
            'processed' => 0,
            'translated' => 0,
            'ignored' => 0,
            'errors' => count($this->gameIds)
        ];
        
        $this->updateBatchStatus($stats, 'failed');
    }
}
