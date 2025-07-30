<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;

echo "🔍 TESTE DE TRADUÇÃO DO GOOGLE\n";
echo "==============================\n\n";

$testText = "This is a test game where the player explores a vast world. You will face many challenges and epic battles.";

echo "📝 Texto original (" . strlen($testText) . " chars):\n";
echo "$testText\n\n";

try {
    $response = Http::timeout(15)->get('https://translate.googleapis.com/translate_a/single', [
        'client' => 'gtx',
        'sl' => 'en',
        'tl' => 'pt',
        'dt' => 't',
        'q' => $testText
    ]);

    if ($response->successful()) {
        $data = $response->json();
        
        echo "📊 Estrutura da resposta:\n";
        if (isset($data[0])) {
            echo "Segmentos encontrados: " . count($data[0]) . "\n\n";
            
            $fullTranslation = '';
            foreach ($data[0] as $index => $segment) {
                echo "Segmento $index:\n";
                var_dump($segment);
                echo "\n";
                
                if (isset($segment[0]) && is_string($segment[0])) {
                    $fullTranslation .= $segment[0];
                }
            }
            
            echo "🌐 Tradução final (" . strlen($fullTranslation) . " chars):\n";
            echo "$fullTranslation\n";
        }
    } else {
        echo "❌ Erro HTTP: " . $response->status() . "\n";
        echo "Corpo: " . $response->body() . "\n";
    }
} catch (Exception $e) {
    echo "❌ Exceção: " . $e->getMessage() . "\n";
}
