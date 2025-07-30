<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Jobs\TranslateGamesBatchJob;
use App\Models\Game;
use App\Models\GameTranslation;

echo "🧪 TESTE DA CORREÇÃO DO JOB BATCH\n";
echo "=================================\n\n";

// Buscar um jogo que sabemos que tem problema
$game = Game::where('title', 'Ancient Enemy')->first();

if (!$game) {
    echo "❌ Jogo não encontrado\n";
    exit;
}

echo "🎮 TESTANDO: {$game->title}\n";
echo "📏 Descrição original: " . strlen($game->description) . " caracteres\n";

// Verificar tradução atual
$currentTranslation = GameTranslation::where('game_id', $game->id)
    ->where('field_name', 'description')
    ->first();

if ($currentTranslation) {
    echo "📊 Tradução atual: " . strlen($currentTranslation->translated_text) . " caracteres\n";
    echo "📅 Última atualização: {$currentTranslation->updated_at}\n\n";
}

// Executar job corrigido
echo "🔧 EXECUTANDO JOB CORRIGIDO...\n";

try {
    $job = new TranslateGamesBatchJob([$game->id], true, 'test_fix_' . time());
    
    // Executar o job diretamente (sem queue)
    $translationService = app(\App\Services\TranslationService::class);
    $rawgService = app(\App\Services\RawgService::class);
    
    $job->handle($translationService, $rawgService);
    
    echo "✅ Job executado com sucesso!\n\n";
    
    // Verificar resultado
    $newTranslation = GameTranslation::where('game_id', $game->id)
        ->where('field_name', 'description')
        ->first();
    
    if ($newTranslation) {
        echo "📊 RESULTADO APÓS CORREÇÃO:\n";
        echo "Tamanho: " . strlen($newTranslation->translated_text) . " caracteres\n";
        echo "Método: {$newTranslation->translation_method}\n";
        echo "Confiança: {$newTranslation->confidence_score}\n";
        echo "Atualizada: {$newTranslation->updated_at}\n";
        echo "Proporção: " . round((strlen($newTranslation->translated_text) / strlen($game->description)) * 100, 1) . "%\n\n";
        
        // Verificar se melhorou
        if ($currentTranslation && strlen($newTranslation->translated_text) > strlen($currentTranslation->translated_text)) {
            $improvement = strlen($newTranslation->translated_text) - strlen($currentTranslation->translated_text);
            echo "🎉 MELHORIA DETECTADA: +{$improvement} caracteres!\n";
            
            if (strlen($newTranslation->translated_text) / strlen($game->description) > 0.7) {
                echo "🏆 QUALIDADE EXCELENTE (>70%)!\n";
            } elseif (strlen($newTranslation->translated_text) / strlen($game->description) > 0.4) {
                echo "✅ BOA QUALIDADE (>40%)!\n";
            } else {
                echo "⚠️ Ainda precisa melhorar\n";
            }
        } else {
            echo "❌ Não houve melhoria significativa\n";
        }
        
        echo "\n📝 AMOSTRA DA TRADUÇÃO:\n";
        echo "\"" . substr($newTranslation->translated_text, 0, 300) . "...\"\n";
    } else {
        echo "❌ Nenhuma tradução encontrada após o job\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Erro na execução: " . $e->getMessage() . "\n";
}

echo "\n✅ Teste concluído!\n";
