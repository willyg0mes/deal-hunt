<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Game;
use App\Models\GameTranslation;
use Illuminate\Support\Facades\DB;

echo "📊 MONITOR DE PROGRESSO DAS TRADUÇÕES\n";
echo "====================================\n\n";

// Função para calcular estatísticas
function calculateStats() {
    $totalGames = Game::whereNotNull('description')
        ->whereRaw('LENGTH(description) > 300')
        ->count();
    
    $totalTranslations = GameTranslation::where('field_name', 'description')
        ->whereHas('game', function($q) {
            $q->whereNotNull('description')
              ->whereRaw('LENGTH(description) > 300');
        })
        ->count();

    // Estatísticas de qualidade
    $translations = GameTranslation::with('game')
        ->where('field_name', 'description')
        ->whereHas('game', function($q) {
            $q->whereNotNull('description')
              ->whereRaw('LENGTH(description) > 300');
        })
        ->get();

    $excellent = 0;
    $good = 0;
    $poor = 0;
    $critical = 0;

    foreach ($translations as $translation) {
        if ($translation->game && $translation->game->description) {
            $ratio = strlen($translation->translated_text) / strlen($translation->game->description);
            
            if ($ratio >= 0.7) {
                $excellent++;
            } elseif ($ratio >= 0.4) {
                $good++;
            } elseif ($ratio >= 0.05) {
                $poor++;
            } else {
                $critical++;
            }
        }
    }

    // Atividade recente
    $recentUpdates = GameTranslation::where('field_name', 'description')
        ->where('updated_at', '>=', now()->subHours(2))
        ->count();

    $veryRecentUpdates = GameTranslation::where('field_name', 'description')
        ->where('updated_at', '>=', now()->subMinutes(30))
        ->count();

    // Jobs na fila
    $queueSize = DB::table('jobs')->count();
    $failedJobs = DB::table('failed_jobs')->count();

    return [
        'total_games' => $totalGames,
        'total_translations' => $totalTranslations,
        'excellent' => $excellent,
        'good' => $good,
        'poor' => $poor,
        'critical' => $critical,
        'recent_2h' => $recentUpdates,
        'recent_30m' => $veryRecentUpdates,
        'queue_size' => $queueSize,
        'failed_jobs' => $failedJobs
    ];
}

// Loop de monitoramento
$iteration = 1;
$maxIterations = 20; // Monitorar por 10 minutos (20 x 30s)

while ($iteration <= $maxIterations) {
    $stats = calculateStats();
    
    echo "🔄 ATUALIZAÇÃO #{$iteration} - " . date('H:i:s') . "\n";
    echo "================================\n";
    
    echo "🎯 COBERTURA:\n";
    echo "Jogos elegíveis: {$stats['total_games']}\n";
    echo "Traduções existentes: {$stats['total_translations']} (" . 
         round(($stats['total_translations'] / max($stats['total_games'], 1)) * 100, 1) . "%)\n\n";
    
    echo "📊 QUALIDADE DAS TRADUÇÕES:\n";
    echo "🏆 Excelentes (≥70%): {$stats['excellent']} (" . 
         round(($stats['excellent'] / max($stats['total_translations'], 1)) * 100, 1) . "%)\n";
    echo "✅ Boas (40-69%): {$stats['good']} (" . 
         round(($stats['good'] / max($stats['total_translations'], 1)) * 100, 1) . "%)\n";
    echo "⚠️ Ruins (5-39%): {$stats['poor']} (" . 
         round(($stats['poor'] / max($stats['total_translations'], 1)) * 100, 1) . "%)\n";
    echo "🔴 Críticas (<5%): {$stats['critical']} (" . 
         round(($stats['critical'] / max($stats['total_translations'], 1)) * 100, 1) . "%)\n\n";
    
    echo "⏰ ATIVIDADE:\n";
    echo "Últimas 2 horas: {$stats['recent_2h']} atualizações\n";
    echo "Últimos 30 min: {$stats['recent_30m']} atualizações\n";
    echo "Jobs na fila: {$stats['queue_size']}\n";
    echo "Jobs falharam: {$stats['failed_jobs']}\n";
    
    // Indicador de atividade
    if ($stats['recent_30m'] > 0) {
        echo "🟢 SISTEMA ATIVO\n";
    } else {
        echo "🔴 SISTEMA PARADO\n";
    }
    
    // Progresso geral
    $qualityScore = round((($stats['excellent'] + $stats['good']) / max($stats['total_translations'], 1)) * 100, 1);
    echo "\n📈 PONTUAÇÃO DE QUALIDADE: {$qualityScore}%\n";
    
    // Estimativa de conclusão
    if ($stats['queue_size'] > 0) {
        $estimatedMinutes = round(($stats['queue_size'] * 8) / 60, 1);
        echo "⏱️ Tempo estimado: {$estimatedMinutes} minutos\n";
    } else {
        echo "✅ Fila vazia - processamento completo\n";
    }
    
    echo "\n" . str_repeat('=', 50) . "\n\n";
    
    // Se chegou ao final ou não há mais jobs
    if ($iteration >= $maxIterations || ($stats['queue_size'] == 0 && $stats['recent_30m'] == 0)) {
        echo "🏁 MONITORAMENTO FINALIZADO\n";
        
        if ($stats['critical'] == 0) {
            echo "🎉 TODAS AS TRADUÇÕES CRÍTICAS FORAM CORRIGIDAS!\n";
        } else {
            echo "⚠️ Ainda há {$stats['critical']} traduções críticas para corrigir\n";
        }
        
        break;
    }
    
    // Aguardar 30 segundos antes da próxima verificação
    sleep(30);
    $iteration++;
}

echo "\n✅ Monitor finalizado!\n";
