<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Game;
use App\Models\GameTranslation;
use App\Services\TranslationService;
use Illuminate\Support\Facades\DB;

echo "🔍 INVESTIGAÇÃO FINAL - PROBLEMA NA ATUALIZAÇÃO\n";
echo "===============================================\n\n";

// Buscar o jogo que testamos
$game = Game::where('title', 'Clash - Artifacts of Chaos')->first();

if (!$game) {
    echo "❌ Jogo não encontrado\n";
    exit;
}

echo "🎮 JOGO: {$game->title}\n";

// Verificar tradução atual
$currentTranslation = GameTranslation::where('game_id', $game->id)
    ->where('field_name', 'description')
    ->first();

echo "📊 TRADUÇÃO ATUAL: " . strlen($currentTranslation->translated_text) . " caracteres\n";
echo "📅 Última atualização: {$currentTranslation->updated_at}\n\n";

// Fazer tradução nova
$translationService = new TranslationService();
$result = $translationService->translateText($game->description);

echo "🧪 NOVA TRADUÇÃO GERADA: " . strlen($result['translation']) . " caracteres\n\n";

// Tentar atualizar manualmente
echo "🔧 TESTANDO ATUALIZAÇÃO MANUAL...\n";

try {
    // Método 1: Update direto
    $updated = $currentTranslation->update([
        'translated_text' => $result['translation'],
        'translation_method' => $result['method'],
        'confidence_score' => $result['confidence'],
        'translated_at' => now()
    ]);
    
    echo "✅ Update executado: " . ($updated ? 'SIM' : 'NÃO') . "\n";
    
    // Verificar novamente
    $currentTranslation->refresh();
    echo "📏 Tamanho após update: " . strlen($currentTranslation->translated_text) . " caracteres\n";
    
    if (strlen($currentTranslation->translated_text) < 500) {
        echo "🚨 AINDA ESTÁ TRUNCADO!\n";
        
        // Tentar via query raw
        echo "🔧 Testando via query raw...\n";
        
        $rawResult = DB::table('game_translations')
            ->where('id', $currentTranslation->id)
            ->update([
                'translated_text' => $result['translation'],
                'updated_at' => now()
            ]);
            
        echo "✅ Raw update executado: " . ($rawResult ? 'SIM' : 'NÃO') . "\n";
        
        // Verificar novamente
        $newCheck = GameTranslation::find($currentTranslation->id);
        echo "📏 Tamanho após raw update: " . strlen($newCheck->translated_text) . " caracteres\n";
        
        if (strlen($newCheck->translated_text) < 500) {
            echo "🚨 PROBLEMA PERSISTE! Pode ser limite da coluna.\n";
            
            // Verificar detalhes da coluna
            echo "\n🔍 INVESTIGANDO ESTRUTURA DA TABELA...\n";
            $columns = DB::select("PRAGMA table_info(game_translations)");
            
            foreach ($columns as $column) {
                if ($column->name === 'translated_text') {
                    echo "Coluna translated_text:\n";
                    echo "  Tipo: {$column->type}\n";
                    echo "  Null: " . ($column->notnull ? 'NÃO' : 'SIM') . "\n";
                    echo "  Default: " . ($column->dflt_value ?? 'NULL') . "\n";
                }
            }
        } else {
            echo "✅ Raw update funcionou!\n";
        }
    } else {
        echo "✅ Update normal funcionou!\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Erro na atualização: " . $e->getMessage() . "\n";
}

echo "\n🔍 Investigação concluída!\n";
