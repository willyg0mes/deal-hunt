<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TranslationService;

class TranslateGamesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'games:translate {--limit=10 : Number of games to translate} {--force : Force retranslation of existing translations} {--recent : Only translate games added in the last 7 days}';

    /**
     * The console command description.
     */
    protected $description = 'Translate game descriptions to Portuguese';

    /**
     * Execute the console command.
     */
    public function handle(TranslationService $translationService)
    {
        $limit = $this->option('limit');
        $force = $this->option('force');
        $recent = $this->option('recent');

        $this->info("🌐 Iniciando tradução de jogos...");
        $this->info("📊 Limite: {$limit} jogos");
        
        if ($force) {
            $this->warn("⚠️  Modo FORCE ativado - retraduzindo jogos existentes");
        }
        
        if ($recent) {
            $this->info("🆕 Traduzindo apenas jogos recentes (últimos 7 dias)");
        }

        $progressBar = $this->output->createProgressBar($limit);
        $progressBar->start();

        $result = $translationService->translateAllGameDescriptions($limit, $force, $recent);

        $progressBar->finish();
        $this->newLine(2);

        $this->info("✅ Tradução concluída!");
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Jogos processados', $result['processed']],
                ['Traduções criadas', $result['success']],
                ['Taxa de sucesso', round(($result['success'] / $result['processed']) * 100, 2) . '%'],
            ]
        );

        return Command::SUCCESS;
    }
}
