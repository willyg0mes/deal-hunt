<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\GameTranslation;
use Illuminate\Support\Facades\DB;

echo "📊 MONITOR DE PROGRESSO DAS TRADUÇÕES CORRIGIDAS\n";
echo "===============================================\n\n";

// Calcular estatísticas gerais
$totalGames = DB::table('games')
    ->whereNotNull('description')
    ->whereRaw('LENGTH(description) > 300')
    ->count();

$totalTranslations = GameTranslation::where('field_name', 'description')
    ->whereHas('game', function($q) {
        $q->whereNotNull('description')
          ->whereRaw('LENGTH(description) > 300');
    })
    ->count();

// Estatísticas de qualidade
$excellentTranslations = GameTranslation::with('game')
    ->where('field_name', 'description')
    ->whereHas('game', function($q) {
        $q->whereNotNull('description')
          ->whereRaw('LENGTH(description) > 300');
    })
    ->get()
    ->filter(function($translation) {
        if (!$translation->game || !$translation->game->description) return false;
        $ratio = strlen($translation->translated_text) / strlen($translation->game->description);
        return $ratio >= 0.7;
    })
    ->count();

$goodTranslations = GameTranslation::with('game')
    ->where('field_name', 'description')
    ->whereHas('game', function($q) {
        $q->whereNotNull('description')
          ->whereRaw('LENGTH(description) > 300');
    })
    ->get()
    ->filter(function($translation) {
        if (!$translation->game || !$translation->game->description) return false;
        $ratio = strlen($translation->translated_text) / strlen($translation->game->description);
        return $ratio >= 0.4 && $ratio < 0.7;
    })
    ->count();

$poorTranslations = GameTranslation::with('game')
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
    ->count();

// Traduções atualizadas nas últimas 2 horas
$recentUpdates = GameTranslation::where('field_name', 'description')
    ->where('updated_at', '>=', now()->subHours(2))
    ->count();

// Progresso nas últimas 30 minutos
$veryRecentUpdates = GameTranslation::where('field_name', 'description')
    ->where('updated_at', '>=', now()->subMinutes(30))
    ->count();

// Calcular porcentagens
$coveragePercent = round(($totalTranslations / max($totalGames, 1)) * 100, 1);
$excellentPercent = round(($excellentTranslations / max($totalTranslations, 1)) * 100, 1);
$goodPercent = round(($goodTranslations / max($totalTranslations, 1)) * 100, 1);
$poorPercent = round(($poorTranslations / max($totalTranslations, 1)) * 100, 1);

echo "🎯 ESTATÍSTICAS GERAIS:\n";
echo "Jogos elegíveis: $totalGames\n";
echo "Traduções existentes: $totalTranslations ($coveragePercent%)\n";
echo "Sem tradução: " . ($totalGames - $totalTranslations) . "\n\n";

echo "📊 QUALIDADE DAS TRADUÇÕES:\n";
echo "🏆 Excelentes (≥70%): $excellentTranslations ($excellentPercent%)\n";
echo "✅ Boas (40-69%): $goodTranslations ($goodPercent%)\n";
echo "⚠️ Ruins (<40%): $poorTranslations ($poorPercent%)\n\n";

echo "⏰ ATIVIDADE RECENTE:\n";
echo "Últimas 2 horas: $recentUpdates traduções atualizadas\n";
echo "Últimos 30 min: $veryRecentUpdates traduções atualizadas\n\n";

// Status dos jobs corrigidos
$correctedJobs = GameTranslation::where('field_name', 'description')
    ->where('updated_at', '>=', now()->subMinutes(10))
    ->where('translation_method', 'google_api')
    ->with('game')
    ->limit(5)
    ->orderBy('updated_at', 'desc')
    ->get();

if ($correctedJobs->count() > 0) {
    echo "🔧 ÚLTIMAS CORREÇÕES (últimos 10 min):\n";
    foreach ($correctedJobs as $correction) {
        $game = $correction->game;
        if ($game && $game->description) {
            $ratio = round((strlen($correction->translated_text) / strlen($game->description)) * 100, 1);
            $quality = $ratio >= 70 ? '🏆 EXCELENTE' : ($ratio >= 40 ? '✅ BOA' : '⚠️ RUIM');
            echo "  • {$game->title}: {$ratio}% {$quality}\n";
        }
    }
    echo "\n";
}

// Verificar se jobs estão rodando
$recentActivity = GameTranslation::where('updated_at', '>=', now()->subMinutes(2))->count();
if ($recentActivity > 0) {
    echo "🟢 SISTEMA ATIVO: $recentActivity traduções nos últimos 2 minutos\n";
} else {
    echo "🔴 SISTEMA PARADO: Nenhuma atividade nos últimos 2 minutos\n";
}

echo "\n📈 PROGRESSO DO PROJETO:\n";
echo "Meta: Traduzir completamente todos os jogos\n";
echo "Traduções de qualidade: " . ($excellentTranslations + $goodTranslations) . " / $totalTranslations (" . 
    round((($excellentTranslations + $goodTranslations) / max($totalTranslations, 1)) * 100, 1) . "%)\n";

echo "\n⏱️ " . date('H:i:s') . " - Monitor executado\n";
