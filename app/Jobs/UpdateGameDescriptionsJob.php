<?php

namespace App\Jobs;

use App\Models\Game;
use App\Services\RawgService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class UpdateGameDescriptionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $gameIds;
    protected $batchId;
    protected $force;

    public $timeout = 600; // 10 minutos por job
    public $tries = 2;

    /**
     * Create a new job instance.
     */
    public function __construct(array $gameIds, string $batchId, bool $force = true)
    {
        $this->gameIds = $gameIds;
        $this->batchId = $batchId;
        $this->force = $force;
    }

    /**
     * Execute the job.
     */
    public function handle(RawgService $rawgService): void
    {
        Log::info("UpdateDescriptions Job {$this->batchId}: Iniciando com " . count($this->gameIds) . " jogos");
        
        $stats = [
            'processed' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0
        ];

        // Atualizar cache com status inicial
        $this->updateBatchStatus($stats, 'processing');

        foreach ($this->gameIds as $index => $gameId) {
            try {
                $game = Game::find($gameId);
                if (!$game) {
                    Log::warning("UpdateDescriptions Job {$this->batchId}: Jogo ID {$gameId} não encontrado");
                    continue;
                }

                $stats['processed']++;
                
                Log::info("UpdateDescriptions Job {$this->batchId}: [{$index}/" . count($this->gameIds) . "] Processando {$game->title}");

                // Verificar se precisa atualizar
                if (!$this->needsUpdate($game)) {
                    $stats['skipped']++;
                    Log::info("UpdateDescriptions Job {$this->batchId}: {$game->title} já tem descrição válida");
                    continue;
                }

                // Buscar nova descrição
                $newDescription = $this->fetchGameDescription($game, $rawgService);
                
                if ($newDescription && strlen($newDescription) > 100) {
                    // Atualizar no banco
                    $game->update(['description' => $newDescription]);
                    $stats['updated']++;
                    
                    Log::info("UpdateDescriptions Job {$this->batchId}: {$game->title} atualizado com " . strlen($newDescription) . " caracteres");
                } else {
                    $stats['skipped']++;
                    Log::warning("UpdateDescriptions Job {$this->batchId}: {$game->title} - descrição não encontrada ou muito curta");
                }

                // Atualizar progresso a cada 5 jogos
                if ($index % 5 === 0) {
                    $this->updateBatchStatus($stats, 'processing', $index + 1, count($this->gameIds));
                }

                // Pausa entre chamadas para não sobrecarregar APIs
                usleep(750000); // 0.75 segundos

            } catch (\Exception $e) {
                $stats['errors']++;
                Log::error("UpdateDescriptions Job {$this->batchId}: Erro ao processar jogo ID {$gameId}: " . $e->getMessage());
            }
        }

        // Finalizar
        $this->updateBatchStatus($stats, 'completed');
        
        Log::info("UpdateDescriptions Job {$this->batchId}: Concluído - " . json_encode($stats));
    }

    /**
     * Verifica se o jogo precisa de atualização de descrição
     */
    private function needsUpdate(Game $game): bool
    {
        if ($this->force) {
            return true;
        }

        // Precisa atualizar se:
        // 1. Não tem descrição
        // 2. Descrição muito curta (< 100 chars)
        // 3. Contém texto genérico de oferta
        return empty($game->description) ||
               strlen($game->description) < 100 ||
               str_contains(strtolower($game->description), 'oferta encontrada') ||
               str_contains(strtolower($game->description), 'em promoção') ||
               str_contains(strtolower($game->description), 'desconto');
    }

    /**
     * Busca descrição completa do jogo usando múltiplas fontes
     */
    private function fetchGameDescription(Game $game, RawgService $rawgService): ?string
    {
        try {
            // 1. Tentar RAWG primeiro (fonte principal)
            Log::info("UpdateDescriptions: Buscando no RAWG para: {$game->title}");
            $rawgData = $rawgService->getGameDetails($game->title);
            
            if (isset($rawgData['description_raw']) && !empty($rawgData['description_raw'])) {
                $description = strip_tags($rawgData['description_raw']);
                if (strlen($description) > 100) {
                    Log::info("UpdateDescriptions: RAWG retornou " . strlen($description) . " caracteres para {$game->title}");
                    return $description;
                }
            }

            // 2. Tentar fontes alternativas
            Log::info("UpdateDescriptions: Tentando fontes alternativas para: {$game->title}");
            $alternativeDesc = $rawgService->getGameDescriptionFromAlternativeSources($game->title);
            if ($alternativeDesc && strlen($alternativeDesc) > 100) {
                Log::info("UpdateDescriptions: Fonte alternativa retornou " . strlen($alternativeDesc) . " caracteres para {$game->title}");
                return $alternativeDesc;
            }

            // 3. Se for DLC ou passe, tentar buscar pelo jogo base
            if ($this->isDLCOrPass($game->title)) {
                $baseGameTitle = $this->extractBaseGameTitle($game->title);
                if ($baseGameTitle !== $game->title) {
                    Log::info("UpdateDescriptions: Tentando busca pelo jogo base: {$baseGameTitle}");
                    
                    $baseGameData = $rawgService->getGameDetails($baseGameTitle);
                    if (isset($baseGameData['description_raw']) && !empty($baseGameData['description_raw'])) {
                        $baseDescription = strip_tags($baseGameData['description_raw']);
                        if (strlen($baseDescription) > 100) {
                            // Adaptar descrição para o DLC/Passe
                            $adaptedDesc = $this->adaptDescriptionForDLC($baseDescription, $game->title);
                            Log::info("UpdateDescriptions: Descrição adaptada do jogo base para {$game->title}");
                            return $adaptedDesc;
                        }
                    }
                }
            }

            Log::warning("UpdateDescriptions: Nenhuma descrição válida encontrada para: {$game->title}");
            return null;

        } catch (\Exception $e) {
            Log::error("UpdateDescriptions: Erro ao buscar descrição para {$game->title}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verifica se é DLC ou Passe de temporada
     */
    private function isDLCOrPass(string $title): bool
    {
        $dlcKeywords = ['dlc', 'expansion', 'season pass', 'passe', 'addon', 'pack', 'bundle', 'edition'];
        $titleLower = strtolower($title);
        
        foreach ($dlcKeywords as $keyword) {
            if (str_contains($titleLower, $keyword)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Extrai o nome do jogo base a partir do título do DLC
     */
    private function extractBaseGameTitle(string $title): string
    {
        // Padrões comuns para extrair jogo base
        $patterns = [
            '/^(.+?)\s*-\s*(dlc|expansion|season pass|passe)/i',
            '/^(.+?)\s*:\s*(dlc|expansion|season pass|passe)/i',
            '/^(.+?)\s+(dlc|expansion|season pass|passe)/i',
            '/^(.+?)\s*\((dlc|expansion|season pass|passe)/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $title, $matches)) {
                return trim($matches[1]);
            }
        }
        
        return $title;
    }

    /**
     * Adapta descrição do jogo base para DLC/Passe
     */
    private function adaptDescriptionForDLC(string $baseDescription, string $dlcTitle): string
    {
        $adapted = "Este conteúdo adicional expande a experiência do jogo base. ";
        $adapted .= $baseDescription;
        
        if (str_contains(strtolower($dlcTitle), 'season pass') || str_contains(strtolower($dlcTitle), 'passe')) {
            $adapted = "Este passe de temporada inclui múltiplos conteúdos adicionais. " . $adapted;
        }
        
        return $adapted;
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

        Cache::put("description_update_{$this->batchId}", $data, now()->addHours(24));
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("UpdateDescriptions Job {$this->batchId} falhou: " . $exception->getMessage());
        
        $stats = [
            'processed' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => count($this->gameIds)
        ];
        
        $this->updateBatchStatus($stats, 'failed');
    }
}
