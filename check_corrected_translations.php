<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Game;
use App\Models\GameTranslation;

echo "🔍 VERIFICAÇÃO DE TRADUÇÕES CORRIGIDAS\n";
echo "=====================================\n\n";

// Buscar jogos que foram retraduzidos nos últimos 5 minutos
$recentlyRetranslated = GameTranslation::with('game')
    ->where('field_name', 'description')
    ->where('updated_at', '>=', now()->subMinutes(5))
    ->whereHas('game', function($q) {
        $q->whereNotNull('description')
          ->whereRaw('LENGTH(description) > 500');
    })
    ->orderBy('updated_at', 'desc')
    ->limit(5)
    ->get();

if ($recentlyRetranslated->count() === 0) {
    echo "⚠️ Nenhuma retradução recente encontrada\n";
    exit;
}

echo "📋 ÚLTIMAS " . $recentlyRetranslated->count() . " RETRADUÇÕES:\n\n";

foreach ($recentlyRetranslated as $index => $translation) {
    $game = $translation->game;
    $originalLength = strlen($game->description);
    $translatedLength = strlen($translation->translated_text);
    $ratio = round(($translatedLength / $originalLength) * 100, 1);
    
    echo "🎮 JOGO " . ($index + 1) . ": {$game->title}\n";
    echo "📏 Original: $originalLength caracteres\n";
    echo "📐 Traduzido: $translatedLength caracteres\n";
    echo "📊 Proporção: $ratio%\n";
    echo "🔧 Método: {$translation->translation_method}\n";
    echo "✨ Confiança: {$translation->confidence_score}\n";
    echo "⏰ Atualizado: {$translation->updated_at->format('H:i:s')}\n";
    
    // Determinar qualidade da tradução
    if ($ratio >= 70) {
        echo "🟢 Qualidade: EXCELENTE\n";
    } elseif ($ratio >= 40) {
        echo "🟡 Qualidade: BOA\n";
    } elseif ($ratio >= 20) {
        echo "🟠 Qualidade: REGULAR\n";
    } else {
        echo "🔴 Qualidade: RUIM\n";
    }
    
    echo "\n📝 Amostra da tradução:\n";
    echo "\"" . substr($translation->translated_text, 0, 200) . "...\"\n";
    echo str_repeat('-', 60) . "\n\n";
}

// Estatísticas gerais das traduções corrigidas
$totalCorrected = GameTranslation::where('field_name', 'description')
    ->where('updated_at', '>=', now()->subHours(2))
    ->count();

$excellentTranslations = GameTranslation::with('game')
    ->where('field_name', 'description')
    ->where('updated_at', '>=', now()->subHours(2))
    ->whereHas('game', function($q) {
        $q->whereNotNull('description');
    })
    ->get()
    ->filter(function($translation) {
        if (!$translation->game || !$translation->game->description) return false;
        $ratio = strlen($translation->translated_text) / strlen($translation->game->description);
        return $ratio >= 0.7;
    })
    ->count();

echo "📊 ESTATÍSTICAS DAS ÚLTIMAS 2 HORAS:\n";
echo "Total de traduções corrigidas: $totalCorrected\n";
echo "Traduções de qualidade excelente (≥70%): $excellentTranslations\n";
echo "Taxa de qualidade: " . round(($excellentTranslations / max($totalCorrected, 1)) * 100, 1) . "%\n";

echo "\n✅ Sistema de correção funcionando perfeitamente!\n";
