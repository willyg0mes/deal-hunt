<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Game;
use App\Models\GameTranslation;
use App\Jobs\TranslateGamesBatchJob;

echo "🔄 CORRIGINDO TRADUÇÕES INCOMPLETAS\n";
echo "====================================\n\n";

// Buscar traduções incompletas (menos de 20% do tamanho original)
$incompleteTranslations = GameTranslation::with('game')
    ->where('field_name', 'description')
    ->whereHas('game', function($q) {
        $q->whereNotNull('description')
          ->whereRaw('LENGTH(description) > 500');
    })
    ->get()
    ->filter(function($translation) {
        if (!$translation->game || !$translation->game->description) {
            return false;
        }
        
        $originalLength = strlen($translation->game->description);
        $translatedLength = strlen($translation->translated_text);
        $ratio = $translatedLength / $originalLength;
        
        return $ratio < 0.3; // Menos de 30% = incompleta
    });

echo "📊 Traduções incompletas encontradas: " . $incompleteTranslations->count() . "\n\n";

if ($incompleteTranslations->count() === 0) {
    echo "✅ Nenhuma tradução incompleta encontrada!\n";
    exit;
}

// Mostrar algumas estatísticas
$stats = $incompleteTranslations->map(function($translation) {
    $originalLength = strlen($translation->game->description);
    $translatedLength = strlen($translation->translated_text);
    return [
        'title' => $translation->game->title,
        'original' => $originalLength,
        'translated' => $translatedLength,
        'ratio' => round(($translatedLength / $originalLength) * 100, 1)
    ];
})->sortBy('ratio');

echo "📋 TOP 10 MAIS INCOMPLETAS:\n";
foreach ($stats->take(10) as $stat) {
    echo "  • {$stat['title']}: {$stat['ratio']}% ({$stat['translated']}/{$stat['original']} chars)\n";
}

echo "\n🎯 Criando jobs para corrigir traduções incompletas...\n";

// Criar jobs para retraduzir jogos com traduções incompletas
$gameIds = $incompleteTranslations->pluck('game_id')->toArray();
$chunks = array_chunk($gameIds, 10); // Chunks menores para processo mais rápido
$batchId = 'retranslation_' . date('YmdHis');

foreach ($chunks as $index => $chunk) {
    $chunkId = $batchId . '_chunk_' . $index;
    TranslateGamesBatchJob::dispatch($chunk, true, $chunkId); // force = true
}

echo "✅ " . count($chunks) . " jobs criados para retradução\n";
echo "📋 Batch ID: $batchId\n";
echo "⏱️ Tempo estimado: " . round((count($gameIds) * 1.5) / 60, 1) . " minutos\n";
echo "🚀 Execute 'php artisan queue:work' para processar\n\n";

echo "🔍 Para monitorar progresso:\n";
echo "  tail -f storage/logs/laravel.log | grep 'TranslateBatch'\n";
