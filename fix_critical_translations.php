<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Game;
use App\Models\GameTranslation;
use App\Services\TranslationService;

echo "🚨 CORREÇÃO ESPECÍFICA PARA TRADUÇÕES EXTREMAMENTE INCOMPLETAS\n";
echo "==============================================================\n\n";

// Buscar traduções extremamente ruins (menos de 5% do original)
$criticallyBadTranslations = GameTranslation::with('game')
    ->where('field_name', 'description')
    ->whereHas('game', function($q) {
        $q->whereNotNull('description')
          ->whereRaw('LENGTH(description) > 500');
    })
    ->get()
    ->filter(function($translation) {
        if (!$translation->game || !$translation->game->description) return false;
        $ratio = strlen($translation->translated_text) / strlen($translation->game->description);
        return $ratio < 0.05; // Menos de 5% = crítico
    })
    ->sortBy(function($translation) {
        return strlen($translation->translated_text) / strlen($translation->game->description);
    });

echo "🔍 Encontradas " . $criticallyBadTranslations->count() . " traduções críticas (<5%)\n\n";

if ($criticallyBadTranslations->count() === 0) {
    echo "✅ Não há traduções críticas para corrigir!\n";
    exit;
}

// Listar as 10 piores
echo "📋 AS 10 PIORES TRADUÇÕES:\n";
foreach ($criticallyBadTranslations->take(10) as $index => $translation) {
    $game = $translation->game;
    $ratio = round((strlen($translation->translated_text) / strlen($game->description)) * 100, 2);
    echo ($index + 1) . ". {$game->title}: {$ratio}% ({$translation->updated_at->format('H:i')})\n";
}
echo "\n";

// Corrigir algumas das piores imediatamente
echo "🔧 CORRIGINDO AS 5 PIORES IMEDIATAMENTE:\n";
echo "=======================================\n";

$translationService = new TranslationService();
$corrected = 0;
$errors = 0;

foreach ($criticallyBadTranslations->take(5) as $index => $translation) {
    $game = $translation->game;
    $originalRatio = round((strlen($translation->translated_text) / strlen($game->description)) * 100, 1);
    
    echo ($index + 1) . ". Corrigindo {$game->title} (atual: {$originalRatio}%)...\n";
    
    try {
        // Fazer nova tradução
        $result = $translationService->translateText($game->description);
        
        if ($result['success'] && strlen($result['translation']) > 100) {
            // Atualizar usando updateOrCreate para garantir atomicidade
            GameTranslation::updateOrCreate(
                [
                    'game_id' => $game->id,
                    'field_name' => 'description'
                ],
                [
                    'original_text' => $game->description,
                    'translated_text' => $result['translation'],
                    'translation_method' => $result['method'],
                    'confidence_score' => $result['confidence'],
                    'translated_at' => now()
                ]
            );
            
            $newRatio = round((strlen($result['translation']) / strlen($game->description)) * 100, 1);
            echo "   ✅ {$originalRatio}% → {$newRatio}% (+". ($newRatio - $originalRatio) ."%) - " . strlen($result['translation']) . " chars\n";
            $corrected++;
        } else {
            echo "   ❌ Falha na tradução\n";
            $errors++;
        }
        
        // Pausa para não sobrecarregar a API
        sleep(1);
        
    } catch (\Exception $e) {
        echo "   💥 Erro: " . $e->getMessage() . "\n";
        $errors++;
    }
}

echo "\n📊 RESULTADO DA CORREÇÃO IMEDIATA:\n";
echo "Corrigidas: $corrected\n";
echo "Erros: $errors\n\n";

// Criar jobs para corrigir o restante
$remainingBadTranslations = $criticallyBadTranslations->skip(5);

if ($remainingBadTranslations->count() > 0) {
    echo "🚀 CRIANDO JOBS PARA CORRIGIR O RESTANTE:\n";
    echo "========================================\n";
    
    $gameIds = $remainingBadTranslations->pluck('game_id')->unique()->toArray();
    $batches = array_chunk($gameIds, 10); // Batches menores para prioridade
    
    $batchId = 'critical_fix_' . date('YmdHis');
    $jobsCreated = 0;
    
    foreach ($batches as $index => $batch) {
        $chunkId = $batchId . '_chunk_' . ($index + 1);
        
        try {
            \App\Jobs\TranslateGamesBatchJob::dispatch($batch, true, $chunkId);
            $jobsCreated++;
            echo "✅ Batch crítico " . ($index + 1) . "/" . count($batches) . " criado - {$chunkId} (" . count($batch) . " jogos)\n";
        } catch (\Exception $e) {
            echo "❌ Erro ao criar batch " . ($index + 1) . ": " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n📋 RESUMO FINAL:\n";
    echo "Jobs críticos criados: $jobsCreated\n";
    echo "Jogos restantes para corrigir: " . count($gameIds) . "\n";
    echo "Batch ID: $batchId\n";
    echo "Prioridade: ALTA (batches pequenos)\n";
}

echo "\n✅ Correção específica concluída!\n";
