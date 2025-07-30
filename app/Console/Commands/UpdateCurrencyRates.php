<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\CurrencyConversionService;

class UpdateCurrencyRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:update-rates {--user= : Update specific user ID} {--force : Force update even if cache is fresh}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update currency exchange rates for all users or a specific user';

    /**
     * Execute the console command.
     */
    public function handle(CurrencyConversionService $currencyService)
    {
        $this->info('🔄 Atualizando taxas de câmbio...');

        // Forçar atualização das taxas se solicitado
        if ($this->option('force')) {
            $currencyService->refreshRates();
            $this->info('✅ Cache de taxas de câmbio limpo');
        }

        // Buscar taxas atualizadas
        $rates = $currencyService->getExchangeRates();
        $this->info('📊 Taxas obtidas para principais moedas');

        // Determinar quais usuários atualizar
        $userId = $this->option('user');
        if ($userId) {
            $users = User::where('id', $userId)->get();
            if ($users->isEmpty()) {
                $this->error("❌ Usuário ID {$userId} não encontrado");
                return 1;
            }
        } else {
            $users = User::all();
        }

        $this->info("🔄 Processando {$users->count()} usuário(s)...");
        
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $updated = 0;
        foreach ($users as $user) {
            try {
                $oldRate = $user->currency_rate;
                $currencyService->updateUserCurrencyRate($user);
                $user->refresh();
                
                if ($oldRate != $user->currency_rate) {
                    $updated++;
                }
                
                $bar->advance();
            } catch (\Exception $e) {
                $this->error("\n❌ Erro ao atualizar usuário {$user->id}: " . $e->getMessage());
            }
        }

        $bar->finish();
        $this->newLine();

        $this->table(['Estatística', 'Quantidade'], [
            ['Usuários processados', $users->count()],
            ['Taxas atualizadas', $updated],
            ['Taxa USD/BRL atual', $rates['BRL'] ?? 'N/A'],
            ['Taxa USD/EUR atual', $rates['EUR'] ?? 'N/A'],
        ]);

        $this->info('✅ Atualização de taxas concluída!');
        return 0;
    }
}
