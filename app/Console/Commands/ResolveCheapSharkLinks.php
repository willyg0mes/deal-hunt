<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Game;
use Illuminate\Support\Facades\Http;

class ResolveCheapSharkLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'games:resolve-links {--limit=10}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resolve alguns links do CheapShark para links diretos das lojas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');
        $this->info("Resolvendo {$limit} links do CheapShark...");
        
        $games = Game::where('deal_url', 'LIKE', '%cheapshark.com/redirect%')
            ->whereNull('resolved_url')
            ->limit($limit)
            ->get();
            
        $resolved = 0;
        $failed = 0;
        
        foreach ($games as $game) {
            $this->line("Resolvendo: {$game->title}");
            
            try {
                // Fazer uma requisição HEAD para obter o redirecionamento
                $response = Http::timeout(10)->head($game->deal_url);
                
                if ($response->successful() && $response->header('Location')) {
                    $resolvedUrl = $response->header('Location');
                    
                    // Atualizar apenas se conseguiu resolver
                    $game->update(['resolved_url' => $resolvedUrl]);
                    $this->info("✓ Resolvido: {$resolvedUrl}");
                    $resolved++;
                } else {
                    $this->warn("✗ Não foi possível resolver o link");
                    $failed++;
                }
                
            } catch (\Exception $e) {
                $this->error("✗ Erro: " . $e->getMessage());
                $failed++;
            }
            
            // Pausa pequena para não sobrecarregar o servidor
            usleep(500000); // 0.5 segundos
        }
        
        $this->info("Concluído! Resolvidos: {$resolved}, Falharam: {$failed}");
    }
}
