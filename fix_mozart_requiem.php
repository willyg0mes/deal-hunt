<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Game;
use App\Models\GameTranslation;
use App\Services\TranslationService;

echo "🎵 CORREÇÃO ESPECÍFICA: MOZART REQUIEM\n";
echo "=====================================\n\n";

// Buscar o Mozart Requiem
$mozartGame = Game::where('title', 'like', '%Mozart%')->first();

if (!$mozartGame) {
    echo "❌ Mozart Requiem não encontrado!\n";
    exit;
}

echo "🎮 JOGO: {$mozartGame->title}\n";
echo "🆔 ID: {$mozartGame->id}\n";
echo "📏 Descrição original: " . strlen($mozartGame->description) . " caracteres\n\n";

// Verificar tradução atual
$currentTranslation = GameTranslation::where('game_id', $mozartGame->id)
    ->where('field_name', 'description')
    ->first();

if ($currentTranslation) {
    $currentRatio = round((strlen($currentTranslation->translated_text) / strlen($mozartGame->description)) * 100, 1);
    echo "📊 TRADUÇÃO ATUAL: {$currentRatio}%\n";
    echo "Texto atual: \"{$currentTranslation->translated_text}\"\n\n";
} else {
    echo "❌ Nenhuma tradução atual encontrada!\n\n";
}

echo "🔧 APLICANDO CORREÇÃO...\n";

try {
    $translationService = new TranslationService();
    
    // Fazer nova tradução
    $result = $translationService->translateText($mozartGame->description);
    
    if ($result['success'] && strlen($result['translation']) > 100) {
        // Usar updateOrCreate para garantir atomicidade
        $translation = GameTranslation::updateOrCreate(
            [
                'game_id' => $mozartGame->id,
                'field_name' => 'description'
            ],
            [
                'original_text' => $mozartGame->description,
                'translated_text' => $result['translation'],
                'translation_method' => $result['method'],
                'confidence_score' => $result['confidence'],
                'translated_at' => now()
            ]
        );
        
        $newRatio = round((strlen($result['translation']) / strlen($mozartGame->description)) * 100, 1);
        
        echo "✅ CORREÇÃO APLICADA COM SUCESSO!\n\n";
        echo "📊 RESULTADO:\n";
        echo "Antes: " . ($currentTranslation ? $currentRatio . "%" : "0%") . "\n";
        echo "Depois: {$newRatio}%\n";
        echo "Melhoria: +" . ($newRatio - ($currentTranslation ? $currentRatio : 0)) . "%\n";
        echo "Tamanho: " . strlen($result['translation']) . " caracteres\n";
        echo "Método: {$result['method']}\n";
        echo "Confiança: {$result['confidence']}\n\n";
        
        echo "📝 NOVA TRADUÇÃO:\n";
        echo "\"" . substr($result['translation'], 0, 500) . "...\"\n\n";
        
        if ($newRatio >= 70) {
            echo "🏆 QUALIDADE EXCELENTE!\n";
        } elseif ($newRatio >= 40) {
            echo "✅ BOA QUALIDADE!\n";
        } else {
            echo "⚠️ Ainda precisa melhorar\n";
        }
        
    } else {
        echo "❌ Falha na tradução: " . ($result['error'] ?? 'Tradução muito curta') . "\n";
    }
    
} catch (\Exception $e) {
    echo "💥 Erro durante a correção: " . $e->getMessage() . "\n";
}

echo "\n✅ Correção do Mozart Requiem concluída!\n";
