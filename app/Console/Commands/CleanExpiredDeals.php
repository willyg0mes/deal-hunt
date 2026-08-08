<?php

namespace App\Console\Commands;

use App\Models\Game;
use Illuminate\Console\Command;

class CleanExpiredDeals extends Command
{
    protected $signature = 'deals:clean-expired
                            {--dry-run : Mostrar o que seria afetado sem alterar}
                            {--force : Executar sem confirmação interativa}
                            {--delete : Apagar do banco em vez de só desativar}';

    protected $description = 'Desativa (ou remove) deals expirados com base em deal_ends_at';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $shouldDelete = $this->option('delete');

        $this->info('🧹 '.($isDryRun ? 'Verificando' : ($shouldDelete ? 'Removendo' : 'Desativando')).' deals expirados...');

        $expiredQuery = Game::query()
            ->where('deal_ends_at', '<', now())
            ->where(function ($q) {
                $q->where('is_free_permanently', false)->orWhereNull('is_free_permanently');
            })
            ->when(! $shouldDelete, fn ($q) => $q->where('is_active', true));

        $expiredCount = (clone $expiredQuery)->count();
        $sample = (clone $expiredQuery)->limit(50)->get();

        if ($expiredCount === 0) {
            $this->info('✅ Nenhum deal expirado pendente!');

            return 0;
        }

        $this->info("📊 Encontrados {$expiredCount} deals expirados (amostra de até 50):");

        $this->table(
            ['ID', 'Título', 'Loja', 'Preço', 'Expirou em'],
            $sample->map(function ($game) {
                return [
                    $game->id,
                    substr($game->title, 0, 30).(strlen($game->title) > 30 ? '...' : ''),
                    $game->store_name,
                    'R$ '.number_format((float) $game->sale_price, 2, ',', '.'),
                    optional($game->deal_ends_at)->format('d/m/Y H:i'),
                ];
            })->toArray()
        );

        if ($isDryRun) {
            $this->warn('🔍 Modo dry-run — nenhuma alteração feita.');

            return 0;
        }

        if (! $this->option('force') && ! $this->confirm("Confirma afetar {$expiredCount} deals?")) {
            $this->info('❌ Operação cancelada.');

            return 1;
        }

        if ($shouldDelete) {
            $affected = $expiredQuery->delete();
            $this->info("✅ {$affected} deals expirados removidos.");
        } else {
            $affected = $expiredQuery->update(['is_active' => false]);
            $this->info("✅ {$affected} deals expirados desativados (permanecem no banco para histórico/wishlist).");
        }

        $this->info('📈 Deals válidos agora: '.Game::valid()->count());

        return 0;
    }
}
