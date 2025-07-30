<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\TranslationService;
use App\Models\Game;
use Illuminate\Support\Facades\Http;

echo "🔍 DIAGNÓSTICO DETALHADO DA API DE TRADUÇÃO\n";
echo "==========================================\n\n";

// Buscar um jogo com descrição longa
$game = Game::whereNotNull('description')
    ->whereRaw('LENGTH(description) > 1000')
    ->where('title', 'like', '%Ancient Enemy%')
    ->first();

if (!$game) {
    echo "❌ Jogo não encontrado\n";
    exit;
}

echo "🎮 JOGO: {$game->title}\n";
echo "📏 Descrição original: " . strlen($game->description) . " caracteres\n\n";

// Mostrar início da descrição
echo "📝 INÍCIO DA DESCRIÇÃO:\n";
echo substr($game->description, 0, 300) . "...\n\n";

// Teste direto da API
echo "🧪 TESTE DIRETO DA API GOOGLE TRANSLATE\n";
echo "=======================================\n\n";

$url = 'https://translate.googleapis.com/translate_a/single';
$params = [
    'client' => 'gtx',
    'sl' => 'en',
    'tl' => 'pt',
    'dt' => 't',
    'q' => $game->description
];

try {
    echo "📡 Fazendo requisição para API...\n";
    $response = Http::timeout(30)->get($url, $params);
    
    if ($response->successful()) {
        $data = $response->json();
        
        echo "✅ Resposta da API recebida\n";
        echo "📊 Status: " . $response->status() . "\n";
        echo "📏 Tamanho da resposta: " . strlen($response->body()) . " bytes\n\n";
        
        // Analisar estrutura da resposta
        echo "🔍 ESTRUTURA DA RESPOSTA:\n";
        if (isset($data[0]) && is_array($data[0])) {
            echo "Número de segmentos: " . count($data[0]) . "\n";
            
            $fullTranslation = '';
            foreach ($data[0] as $index => $segment) {
                if (isset($segment[0]) && is_string($segment[0])) {
                    $segmentText = $segment[0];
                    $segmentLength = strlen($segmentText);
                    echo "Segmento $index: $segmentLength caracteres\n";
                    $fullTranslation .= $segmentText;
                    
                    // Mostrar primeira parte de cada segmento
                    if ($segmentLength > 0) {
                        echo "  └─ \"" . substr($segmentText, 0, 100) . "...\"\n";
                    }
                }
            }
            
            echo "\n📐 TRADUÇÃO COMPLETA:\n";
            echo "Tamanho total: " . strlen($fullTranslation) . " caracteres\n";
            echo "Proporção: " . round((strlen($fullTranslation) / strlen($game->description)) * 100, 1) . "%\n\n";
            
            echo "📝 TRADUÇÃO RESULTANTE:\n";
            echo "\"" . substr($fullTranslation, 0, 500) . "...\"\n\n";
            
        } else {
            echo "❌ Estrutura de resposta inesperada\n";
            echo "Dados recebidos: " . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }
        
    } else {
        echo "❌ Erro na requisição: " . $response->status() . "\n";
        echo "Corpo da resposta: " . $response->body() . "\n";
    }
    
} catch (\Exception $e) {
    echo "💥 Exceção: " . $e->getMessage() . "\n";
}

// Teste com texto menor para comparação
echo "\n🧪 TESTE COM TEXTO MENOR (300 caracteres)\n";
echo "========================================\n";

$shortText = substr($game->description, 0, 300);
$params['q'] = $shortText;

try {
    $response = Http::timeout(30)->get($url, $params);
    
    if ($response->successful()) {
        $data = $response->json();
        
        if (isset($data[0]) && is_array($data[0])) {
            $shortTranslation = '';
            foreach ($data[0] as $segment) {
                if (isset($segment[0]) && is_string($segment[0])) {
                    $shortTranslation .= $segment[0];
                }
            }
            
            echo "✅ Texto curto traduzido\n";
            echo "Original: " . strlen($shortText) . " caracteres\n";
            echo "Traduzido: " . strlen($shortTranslation) . " caracteres\n";
            echo "Proporção: " . round((strlen($shortTranslation) / strlen($shortText)) * 100, 1) . "%\n";
            echo "Tradução: \"$shortTranslation\"\n";
        }
    }
} catch (\Exception $e) {
    echo "💥 Exceção no teste curto: " . $e->getMessage() . "\n";
}

echo "\n✅ Diagnóstico concluído!\n";
