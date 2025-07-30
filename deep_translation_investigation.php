<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\TranslationService;
use App\Models\Game;
use App\Models\GameTranslation;

echo "🕵️ INVESTIGAÇÃO PROFUNDA DA TRADUÇÃO\n";
echo "====================================\n\n";

// Buscar um jogo que foi retraduzido recentemente
$game = Game::where('title', 'Clash - Artifacts of Chaos')
    ->orWhere('title', 'PAYDAY 2')
    ->first();

if (!$game) {
    echo "❌ Jogo não encontrado\n";
    exit;
}

echo "🎮 INVESTIGANDO: {$game->title}\n";
echo "📏 Descrição original: " . strlen($game->description) . " caracteres\n\n";

// Verificar a tradução atual no banco
$currentTranslation = GameTranslation::where('game_id', $game->id)
    ->where('field_name', 'description')
    ->first();

if ($currentTranslation) {
    echo "📊 TRADUÇÃO ATUAL NO BANCO:\n";
    echo "Tamanho: " . strlen($currentTranslation->translated_text) . " caracteres\n";
    echo "Método: {$currentTranslation->translation_method}\n";
    echo "Confiança: {$currentTranslation->confidence_score}\n";
    echo "Atualizada: {$currentTranslation->updated_at}\n";
    echo "Proporção: " . round((strlen($currentTranslation->translated_text) / strlen($game->description)) * 100, 1) . "%\n\n";
    
    echo "📝 CONTEÚDO ATUAL:\n";
    echo "\"" . $currentTranslation->translated_text . "\"\n\n";
}

// Fazer uma nova tradução usando o serviço diretamente
echo "🧪 TESTE DIRETO COM O SERVIÇO DE TRADUÇÃO:\n";
echo "==========================================\n";

$translationService = new TranslationService();

// Ativar logs detalhados temporariamente
\Illuminate\Support\Facades\Log::info("TESTE DIRETO: Iniciando tradução para {$game->title}");

$result = $translationService->translateText($game->description);

echo "✅ RESULTADO DO SERVIÇO:\n";
echo "Sucesso: " . ($result['success'] ? 'SIM' : 'NÃO') . "\n";
echo "Método: " . $result['method'] . "\n";
echo "Confiança: " . $result['confidence'] . "\n";

if ($result['success']) {
    echo "Tamanho da tradução: " . strlen($result['translation']) . " caracteres\n";
    echo "Proporção: " . round((strlen($result['translation']) / strlen($game->description)) * 100, 1) . "%\n\n";
    
    echo "📝 TRADUÇÃO GERADA PELO SERVIÇO:\n";
    echo "\"" . substr($result['translation'], 0, 500) . "...\"\n\n";
    
    // Comparar com o que está no banco
    if ($currentTranslation) {
        echo "🔍 COMPARAÇÃO:\n";
        echo "Banco: " . strlen($currentTranslation->translated_text) . " chars\n";
        echo "Serviço: " . strlen($result['translation']) . " chars\n";
        echo "Diferença: " . (strlen($result['translation']) - strlen($currentTranslation->translated_text)) . " chars\n";
        
        if (strlen($result['translation']) != strlen($currentTranslation->translated_text)) {
            echo "⚠️ DISCREPÂNCIA DETECTADA! O serviço gera tradução diferente do banco\n";
        } else {
            echo "✅ Tamanhos coincidem\n";
        }
    }
} else {
    echo "❌ Erro: " . ($result['error'] ?? 'Desconhecido') . "\n";
}

echo "\n🔍 Verificação concluída!\n";
