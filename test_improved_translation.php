<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Game;
use App\Services\TranslationService;

echo "🧪 TESTE DE TRADUÇÃO CORRIGIDA\n";
echo "===============================\n\n";

// Buscar um jogo específico que foi traduzido recentemente mas de forma incompleta
$game = Game::whereHas('translations', function($q) {
    $q->where('field_name', 'description')
      ->where('created_at', '>=', now()->subMinutes(30));
})
->whereNotNull('description')
->whereRaw('LENGTH(description) > 1000')
->first();

if (!$game) {
    echo "❌ Nenhum jogo encontrado para reteste\n";
    exit;
}

echo "🎮 Testando com: {$game->title}\n";
echo "📏 Descrição original: " . strlen($game->description) . " caracteres\n";

// Buscar tradução existente
$existingTranslation = $game->translations()
    ->where('field_name', 'description')
    ->first();

if ($existingTranslation) {
    echo "📐 Tradução atual: " . strlen($existingTranslation->translated_text) . " caracteres\n";
    echo "📊 Proporção atual: " . round((strlen($existingTranslation->translated_text) / strlen($game->description)) * 100, 1) . "%\n\n";
}

echo "🔄 Executando nova tradução com sistema corrigido...\n\n";

// Testar nova tradução
$translationService = app(TranslationService::class);
$newTranslation = $translationService->translateGameDescription($game, true); // Force = true

if ($newTranslation) {
    echo "✅ Nova tradução criada!\n";
    echo "📐 Nova tradução: " . strlen($newTranslation->translated_text) . " caracteres\n";
    echo "📊 Nova proporção: " . round((strlen($newTranslation->translated_text) / strlen($game->description)) * 100, 1) . "%\n";
    echo "🔧 Método: {$newTranslation->translation_method}\n";
    echo "✨ Confiança: {$newTranslation->confidence_score}\n\n";
    
    echo "📝 Amostra da nova tradução:\n";
    echo "\"" . substr($newTranslation->translated_text, 0, 300) . "...\"\n";
    
    if ($existingTranslation) {
        $improvement = strlen($newTranslation->translated_text) - strlen($existingTranslation->translated_text);
        echo "\n📈 Melhoria: " . ($improvement > 0 ? "+$improvement" : "$improvement") . " caracteres\n";
    }
} else {
    echo "❌ Falha na nova tradução\n";
}

echo "\n🔍 Verificar logs para mais detalhes sobre o processo\n";
