<?php

namespace App\Console\Commands;

use App\Models\Game;
use Illuminate\Console\Command;

class CleanExpiredDeals extends Command
{
    protected $signature = 'deals:clean-expired {--dry-run : Mostrar o que seria removido sem remover}';
    protected $description = 'Remove deals que já expiraram baseado na data deal_ends_at';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        $this->info('🧹 ' . ($isDryRun ? 'Verificando' : 'Limpando') . ' deals expirados...');
        
        // Buscar deals expirados
        $expiredDeals = Game::where('deal_ends_at', '<', now())->get();
        
        if ($expiredDeals->isEmpty()) {
            $this->info('✅ Nenhum deal expirado encontrado!');
            return;
        }
        
        $this->info("📊 Encontrados {$expiredDeals->count()} deals expirados:");
        
        // Exibir deals que serão removidos
        $this->table(
            ['ID', 'Título', 'Loja', 'Preço', 'Expirou em'],
            $expiredDeals->map(function ($game) {
                return [
                    $game->id,
                    substr($game->title, 0, 30) . (strlen($game->title) > 30 ? '...' : ''),
                    $game->store_name,
                    'R$ ' . number_format($game->sale_price, 2, ',', '.'),
                    $game->deal_ends_at->format('d/m/Y H:i')
                ];
            })->toArray()
        );
        
        if ($isDryRun) {
            $this->warn('🔍 Modo dry-run ativo - nenhum deal foi removido.');
            $this->info('Execute sem --dry-run para remover os deals expirados.');
            return;
        }
        
        // Confirmar remoção
        if (!$this->confirm("Deseja remover {$expiredDeals->count()} deals expirados?")) {
            $this->info('❌ Operação cancelada.');
            return;
        }
        
        // Remover deals expirados
        $deletedCount = Game::where('deal_ends_at', '<', now())->delete();
        
        $this->info("✅ {$deletedCount} deals expirados removidos com sucesso!");
        
        // Estatísticas finais
        $remainingDeals = Game::count();
        $this->info("📈 Deals ativos restantes: {$remainingDeals}");
    }
}
