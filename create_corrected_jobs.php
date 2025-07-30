<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Game;
use App\Models\GameTranslation;
use App\Jobs\TranslateGamesBatchJob;

echo "🚀 CRIANDO NOVA RODADA DE JOBS CORRIGIDOS\n";
echo "=========================================\n\n";

// Buscar traduções incompletas (menos de 30% do original)
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
        return $ratio < 0.3; // Menos de 30% = incompleta
    });

echo "🔍 Encontradas " . $incompleteTranslations->count() . " traduções incompletas (<30%)\n\n";

if ($incompleteTranslations->count() === 0) {
    echo "✅ Não há traduções incompletas para corrigir!\n";
    exit;
}

// Agrupar em batches de 15 (tamanho otimizado)
$gameIds = $incompleteTranslations->pluck('game_id')->toArray();
$batches = array_chunk($gameIds, 15);

$batchId = 'corrected_' . date('YmdHis');

echo "📦 Criando " . count($batches) . " batches de retradução corrigida:\n\n";

$jobsCreated = 0;

foreach ($batches as $index => $batch) {
    $chunkId = $batchId . '_chunk_' . ($index + 1);
    
    try {
        // Criar job com force=true para garantir retradução
        TranslateGamesBatchJob::dispatch($batch, true, $chunkId);
        
        $jobsCreated++;
        echo "✅ Batch " . ($index + 1) . "/" . count($batches) . " criado - {$chunkId} (" . count($batch) . " jogos)\n";
        
        // Pausa pequena entre criação de jobs
        if ($index < count($batches) - 1) {
            usleep(100000); // 0.1 segundos
        }
        
    } catch (\Exception $e) {
        echo "❌ Erro ao criar batch " . ($index + 1) . ": " . $e->getMessage() . "\n";
    }
}

echo "\n📊 RESUMO:\n";
echo "Jobs criados: $jobsCreated\n";
echo "Jogos para retraduzir: " . count($gameIds) . "\n";
echo "Batch ID: $batchId\n";

echo "\n🚀 Jobs de correção despachados! Use os comandos abaixo para monitorar:\n";
echo "php artisan queue:work --sleep=1 --tries=3\n";
echo "tail -f storage/logs/laravel.log | grep 'corrected_'\n";

echo "\n✅ Operação concluída!\n";
