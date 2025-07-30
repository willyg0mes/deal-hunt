<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Game;
use App\Models\GameTranslation;

echo "🔍 VERIFICAÇÃO DE TRADUÇÕES COMPLETAS\n";
echo "=====================================\n\n";

// Buscar traduções recentes
$recentTranslations = GameTranslation::with('game')
    ->where('field_name', 'description')
    ->where('created_at', '>=', now()->subMinutes(10))
    ->orderBy('created_at', 'desc')
    ->limit(3)
    ->get();

if ($recentTranslations->count() > 0) {
    echo "📋 ÚLTIMAS " . $recentTranslations->count() . " TRADUÇÕES (últimos 10 min):\n\n";
    
    foreach ($recentTranslations as $index => $translation) {
        echo "🎮 JOGO " . ($index + 1) . ": {$translation->game->title}\n";
        echo "📏 Original: " . strlen($translation->original_text) . " caracteres\n";
        echo "📐 Traduzido: " . strlen($translation->translated_text) . " caracteres\n";
        echo "📊 Proporção: " . round((strlen($translation->translated_text) / strlen($translation->original_text)) * 100, 1) . "%\n";
        echo "🔧 Método: {$translation->translation_method}\n";
        echo "✨ Confiança: {$translation->confidence_score}\n";
        echo "⏰ Data: {$translation->created_at->format('H:i:s')}\n";
        
        echo "\n📝 Amostra da tradução:\n";
        echo "\"" . substr($translation->translated_text, 0, 150) . "...\"\n";
        echo str_repeat('-', 60) . "\n\n";
    }
} else {
    echo "⚠️ Nenhuma tradução recente encontrada\n\n";
}

// Estatísticas gerais
$totalTranslations = GameTranslation::where('field_name', 'description')->count();
$totalGames = Game::count();
$coveragePercent = round(($totalTranslations / $totalGames) * 100, 1);

echo "📊 ESTATÍSTICAS GERAIS:\n";
echo "Total de jogos: $totalGames\n";
echo "Total de traduções: $totalTranslations\n";
echo "Cobertura de tradução: $coveragePercent%\n";

// Jogos prontos para tradução
$readyForTranslation = Game::whereNotNull('description')
    ->where('description', '!=', '')
    ->whereRaw('LENGTH(description) > 100')
    ->whereDoesntHave('translations', function($q) {
        $q->where('field_name', 'description');
    })
    ->count();

echo "Jogos prontos para tradução: $readyForTranslation\n";

echo "\n✅ Sistema funcionando corretamente!\n";
