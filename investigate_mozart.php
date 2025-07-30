<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Game;
use App\Models\GameTranslation;
use App\Services\TranslationService;

echo "🔍 INVESTIGAÇÃO: MOZART REQUIEM E TRADUÇÕES INCOMPLETAS\n";
echo "======================================================\n\n";

// Buscar o jogo Mozart Requiem
$mozartGame = Game::where('title', 'like', '%Mozart%')
    ->orWhere('title', 'like', '%Requiem%')
    ->first();

if ($mozartGame) {
    echo "🎮 JOGO ENCONTRADO: {$mozartGame->title}\n";
    echo "📏 Descrição original: " . strlen($mozartGame->description) . " caracteres\n";
    echo "🆔 Game ID: {$mozartGame->id}\n\n";
    
    // Verificar tradução atual
    $currentTranslation = GameTranslation::where('game_id', $mozartGame->id)
        ->where('field_name', 'description')
        ->first();
    
    if ($currentTranslation) {
        $ratio = round((strlen($currentTranslation->translated_text) / strlen($mozartGame->description)) * 100, 1);
        echo "📊 TRADUÇÃO ATUAL:\n";
        echo "Tamanho: " . strlen($currentTranslation->translated_text) . " caracteres\n";
        echo "Proporção: {$ratio}%\n";
        echo "Método: {$currentTranslation->translation_method}\n";
        echo "Confiança: {$currentTranslation->confidence_score}\n";
        echo "Atualizada: {$currentTranslation->updated_at}\n\n";
        
        echo "📝 TRADUÇÃO ATUAL:\n";
        echo "\"" . $currentTranslation->translated_text . "\"\n\n";
        
        if ($ratio < 50) {
            echo "🚨 TRADUÇÃO INCOMPLETA DETECTADA!\n\n";
        }
    } else {
        echo "❌ Nenhuma tradução encontrada!\n\n";
    }
    
    echo "📝 DESCRIÇÃO ORIGINAL:\n";
    echo "\"" . substr($mozartGame->description, 0, 500) . "...\"\n\n";
} else {
    echo "❌ Jogo Mozart Requiem não encontrado. Listando jogos com 'Mozart' ou 'Requiem':\n";
    
    $games = Game::where('title', 'like', '%Mozart%')
        ->orWhere('title', 'like', '%Requiem%')
        ->get();
    
    foreach ($games as $game) {
        echo "- {$game->title} (ID: {$game->id})\n";
    }
    echo "\n";
}

// Buscar outras traduções incompletas (menos de 40%)
echo "🔍 OUTRAS TRADUÇÕES INCOMPLETAS (<40%):\n";
echo "=====================================\n";

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
        return $ratio < 0.4;
    })
    ->sortBy(function($translation) {
        return strlen($translation->translated_text) / strlen($translation->game->description);
    })
    ->take(10);

foreach ($incompleteTranslations as $translation) {
    $game = $translation->game;
    $ratio = round((strlen($translation->translated_text) / strlen($game->description)) * 100, 1);
    $lastUpdate = $translation->updated_at->diffForHumans();
    
    echo "• {$game->title}: {$ratio}% ({$lastUpdate})\n";
    echo "  Original: " . strlen($game->description) . " chars | Traduzido: " . strlen($translation->translated_text) . " chars\n\n";
}

// Verificar se há jobs corrected_ ainda rodando
echo "🔧 STATUS DOS JOBS CORRIGIDOS:\n";
echo "=============================\n";

$recentCorrectedTranslations = GameTranslation::where('field_name', 'description')
    ->where('updated_at', '>=', now()->subHours(1))
    ->whereHas('game', function($q) {
        $q->whereNotNull('description');
    })
    ->with('game')
    ->get()
    ->filter(function($translation) {
        if (!$translation->game) return false;
        $ratio = strlen($translation->translated_text) / strlen($translation->game->description);
        return $ratio >= 0.7; // Traduções de qualidade
    })
    ->count();

echo "Traduções de qualidade (≥70%) na última hora: $recentCorrectedTranslations\n";

// Verificar jobs na fila
$jobsInQueue = \Illuminate\Support\Facades\DB::table('jobs')->count();
echo "Jobs na fila: $jobsInQueue\n\n";

echo "✅ Investigação concluída!\n";
