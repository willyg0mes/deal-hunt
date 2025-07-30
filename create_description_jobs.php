<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Game;
use App\Jobs\UpdateGameDescriptionsJob;

echo "🎯 Iniciando criação de jobs para atualização de descrições...\n";

// Buscar jogos que precisam de atualização
$gamesNeedingUpdate = Game::where(function($q) {
    $q->whereNull('description')
      ->orWhere('description', '=', '')
      ->orWhereRaw('LENGTH(description) < 100')
      ->orWhereRaw('LOWER(description) LIKE ?', ['%oferta encontrada%'])
      ->orWhereRaw('LOWER(description) LIKE ?', ['%em promoção%'])
      ->orWhereRaw('LOWER(description) LIKE ?', ['%desconto%']);
})->limit(3154)->pluck('id')->toArray();

echo "📊 Encontrados " . count($gamesNeedingUpdate) . " jogos que precisam de atualização\n";

if (empty($gamesNeedingUpdate)) {
    echo "❌ Nenhum jogo encontrado para atualizar\n";
    exit(1);
}

// Dividir em chunks de 15 jogos
$chunks = array_chunk($gamesNeedingUpdate, 15);
$batchId = 'desc_update_' . date('YmdHis');

echo "📦 Criando " . count($chunks) . " chunks de 15 jogos cada\n";
echo "🆔 Batch ID: $batchId\n";

// Disparar os jobs
$jobsCreated = 0;
foreach ($chunks as $index => $chunk) {
    $chunkId = $batchId . '_chunk_' . $index;
    UpdateGameDescriptionsJob::dispatch($chunk, $chunkId, true);
    $jobsCreated++;
    
    // Mostrar progresso a cada 10 jobs
    if ($jobsCreated % 10 === 0) {
        echo "📋 Criados $jobsCreated de " . count($chunks) . " jobs...\n";
    }
}

echo "✅ $jobsCreated jobs criados com sucesso!\n";
echo "⏱️ Tempo estimado: " . round((count($gamesNeedingUpdate) * 0.75) / 60, 1) . " minutos\n";
echo "🚀 Execute 'php artisan queue:work' para processar\n";
echo "📋 Batch ID: $batchId\n";

// Salvar batch ID para monitoramento
file_put_contents(__DIR__ . '/last_batch_id.txt', $batchId);
echo "💾 Batch ID salvo em 'last_batch_id.txt'\n";
