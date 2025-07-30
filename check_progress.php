<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Game;
use Illuminate\Support\Facades\DB;

echo "📊 RELATÓRIO DE PROGRESSO - ATUALIZAÇÃO DE DESCRIÇÕES\n";
echo "====================================================\n\n";

// Jobs
$totalJobs = DB::table('jobs')->count();
$failedJobs = DB::table('failed_jobs')->count();

echo "🔄 FILA DE PROCESSAMENTO:\n";
echo "   Jobs pendentes: $totalJobs\n";
echo "   Jobs falhados: $failedJobs\n\n";

// Estatísticas de descrições
$totalGames = Game::count();

$withoutDescription = Game::where(function($q) {
    $q->whereNull('description')->orWhere('description', '=', '');
})->count();

$shortDescription = Game::whereNotNull('description')
    ->where('description', '!=', '')
    ->whereRaw('LENGTH(description) < 100')
    ->count();

$validDescription = Game::whereNotNull('description')
    ->where('description', '!=', '')
    ->whereRaw('LENGTH(description) >= 100')
    ->whereRaw('LOWER(description) NOT LIKE ?', ['%oferta encontrada%'])
    ->count();

$genericDescription = Game::whereNotNull('description')
    ->where('description', '!=', '')
    ->where(function($q) {
        $q->whereRaw('LOWER(description) LIKE ?', ['%oferta encontrada%'])
          ->orWhereRaw('LOWER(description) LIKE ?', ['%em promoção%'])
          ->orWhereRaw('LOWER(description) LIKE ?', ['%desconto%']);
    })->count();

$coverage = round(($validDescription / $totalGames) * 100, 1);
$needUpdate = $withoutDescription + $shortDescription + $genericDescription;

echo "📈 ESTATÍSTICAS DE DESCRIÇÕES:\n";
echo "   Total de jogos: $totalGames\n";
echo "   Sem descrição: $withoutDescription\n";
echo "   Descrição curta: $shortDescription\n";
echo "   Descrição válida: $validDescription\n";
echo "   Descrição genérica: $genericDescription\n";
echo "   Precisam atualizar: $needUpdate\n";
echo "   Cobertura: $coverage%\n\n";

// Atualizações recentes
$recentlyUpdated = Game::where('updated_at', '>=', now()->subMinutes(30))
    ->whereNotNull('description')
    ->where('description', '!=', '')
    ->whereRaw('LENGTH(description) >= 100')
    ->count();

echo "🔄 ATIVIDADE RECENTE (últimos 30 min):\n";
echo "   Jogos atualizados: $recentlyUpdated\n\n";

// Melhores descrições encontradas recentemente
$recentBest = Game::where('updated_at', '>=', now()->subHours(2))
    ->whereNotNull('description')
    ->where('description', '!=', '')
    ->whereRaw('LENGTH(description) >= 500')
    ->orderBy('updated_at', 'desc')
    ->limit(5)
    ->get(['title', 'updated_at']);

if ($recentBest->count() > 0) {
    echo "🏆 MELHORES ATUALIZAÇÕES RECENTES (>500 chars):\n";
    foreach ($recentBest as $game) {
        echo "   - {$game->title} ({$game->updated_at->format('H:i:s')})\n";
    }
    echo "\n";
}

echo "📋 Batch ID atual: ";
if (file_exists(__DIR__ . '/last_batch_id.txt')) {
    echo file_get_contents(__DIR__ . '/last_batch_id.txt') . "\n";
} else {
    echo "N/A\n";
}

echo "\n✅ Relatório atualizado em: " . date('Y-m-d H:i:s') . "\n";
