<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Services\RawgService;
use Illuminate\Console\Command;

class UpdateGameImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'games:update-images {--limit=50 : Limite de jogos para atualizar} {--force : Atualizar mesmo jogos que já têm imagens}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atualiza as imagens dos jogos para capas oficiais de melhor qualidade';

    protected RawgService $rawgService;

    public function __construct(RawgService $rawgService)
    {
        parent::__construct();
        $this->rawgService = $rawgService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');
        $force = $this->option('force');

        $this->info("🎮 Atualizando imagens dos jogos...");
        $this->info("Limite: {$limit} jogos");
        $this->info("Forçar atualização: " . ($force ? 'SIM' : 'NÃO'));
        $this->newLine();

        // Buscar jogos para atualizar
        $query = Game::query();
        
        if (!$force) {
            // Se não forçar, atualizar apenas jogos com imagens placeholder ou screenshots
            $query->where(function($q) {
                $q->where('image', 'LIKE', '%placeholder%')
                  ->orWhere('image', 'LIKE', '%screenshot%')
                  ->orWhereNull('image');
            });
        }

        $games = $query->limit($limit)->get();

        if ($games->isEmpty()) {
            $this->warn('Nenhum jogo encontrado para atualizar.');
            return 0;
        }

        $this->info("Encontrados {$games->count()} jogos para atualizar.");
        $this->newLine();

        $bar = $this->output->createProgressBar($games->count());
        $bar->start();

        $updated = 0;
        $failed = 0;

        foreach ($games as $game) {
            try {
                $oldImage = $game->image;
                $newImage = $this->rawgService->getBestGameImage($game->title);

                if ($newImage && $newImage !== $oldImage) {
                    $game->update(['image' => $newImage]);
                    $updated++;
                    
                    if ($this->output->isVerbose()) {
                        $this->newLine();
                        $this->line("✅ {$game->title}: Nova imagem encontrada");
                    }
                } else {
                    if ($this->output->isVerbose()) {
                        $this->newLine();
                        $this->line("⚠️  {$game->title}: Nenhuma melhoria encontrada");
                    }
                }

                // Pequena pausa para não sobrecarregar as APIs
                usleep(200000); // 200ms

            } catch (\Exception $e) {
                $failed++;
                if ($this->output->isVerbose()) {
                    $this->newLine();
                    $this->error("❌ {$game->title}: Erro - {$e->getMessage()}");
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Atualização concluída!");
        $this->table(['Estatística', 'Quantidade'], [
            ['Jogos processados', $games->count()],
            ['Imagens atualizadas', $updated],
            ['Falhas', $failed],
            ['Taxa de sucesso', round(($updated / $games->count()) * 100, 1) . '%']
        ]);

        return 0;
    }
}
