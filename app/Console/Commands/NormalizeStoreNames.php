<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Game;

class NormalizeStoreNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'games:normalize-stores';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Normaliza os nomes das lojas no banco de dados';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando normalização dos nomes das lojas...');
        
        $updated = 0;
        
        // Atualizar todos os jogos que não têm store_name preenchido
        Game::whereNull('store_name')->orWhere('store_name', '')->chunk(100, function($games) use (&$updated) {
            foreach ($games as $game) {
                $storeName = $game->store_name; // Isso vai calcular o nome usando o accessor
                $game->update(['store_name' => $storeName]);
                $updated++;
            }
        });
        
        $this->info("Total de jogos atualizados: {$updated}");
        $this->info('Normalização concluída!');
        
        // Mostrar lojas normalizadas
        $this->info('Lojas normalizadas no banco:');
        $stores = Game::distinct()->pluck('store_name')->sort();
        foreach ($stores as $store) {
            $count = Game::where('store_name', $store)->count();
            $this->line("- {$store}: {$count} jogos");
        }
    }
}
