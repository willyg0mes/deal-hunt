<?php

namespace App\Services;

use App\Models\Game;
use App\Models\GameTranslation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    /**
     * Traduz um texto usando múltiplas estratégias
     */
    public function translateText($text, $targetLanguage = 'pt')
    {
        try {
            // 1. Primeiro tenta tradução via Google Translate (gratuita limitada)
            $translation = $this->translateWithGoogle($text, $targetLanguage);
            
            if ($translation) {
                return [
                    'success' => true,
                    'translation' => $translation,
                    'method' => 'google_api',
                    'confidence' => 0.8
                ];
            }

            // 2. Fallback para tradução local (palavras-chave)
            $localTranslation = $this->translateLocally($text);
            
            return [
                'success' => true,
                'translation' => $localTranslation,
                'method' => 'local_keywords',
                'confidence' => 0.6
            ];
        } catch (\Exception $e) {
            Log::error('Erro na tradução: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'translation' => null,
                'method' => 'failed',
                'confidence' => 0
            ];
        }
    }

    /**
     * Tradução usando Google Translate (versão gratuita via web)
     */
    private function translateWithGoogle($text, $targetLang = 'pt')
    {
        try {
            // Para textos muito longos (>1500 chars), dividir em chunks
            if (strlen($text) > 1500) {
                return $this->translateLongText($text, $targetLang);
            }

            // Usar múltiplas abordagens para garantir tradução completa
            $translation = $this->tryMultipleTranslationMethods($text, $targetLang);
            
            if ($translation && strlen($translation) > 50) {
                Log::info("Google Translate: Texto original " . strlen($text) . " chars -> Traduzido " . strlen($translation) . " chars");
                return $translation;
            }

        } catch (\Exception $e) {
            Log::warning('Google Translate failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Tenta múltiplos métodos de tradução para garantir completude
     */
    private function tryMultipleTranslationMethods($text, $targetLang = 'pt')
    {
        $methods = [
            // Método 1: API oficial gratuita
            [
                'url' => 'https://translate.googleapis.com/translate_a/single',
                'params' => [
                    'client' => 'gtx',
                    'sl' => 'en',
                    'tl' => $targetLang,
                    'dt' => 't',
                    'q' => $text
                ]
            ],
            // Método 2: Parâmetros alternativos
            [
                'url' => 'https://translate.googleapis.com/translate_a/single',
                'params' => [
                    'client' => 'webapp',
                    'sl' => 'en',
                    'tl' => $targetLang,
                    'dt' => 't',
                    'dt' => 'bd',
                    'q' => $text
                ]
            ]
        ];

        foreach ($methods as $method) {
            try {
                $response = Http::timeout(20)->get($method['url'], $method['params']);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    // Concatenar todos os segmentos da tradução
                    $fullTranslation = '';
                    if (isset($data[0]) && is_array($data[0])) {
                        foreach ($data[0] as $segment) {
                            if (isset($segment[0]) && is_string($segment[0])) {
                                $fullTranslation .= $segment[0];
                            }
                        }
                    }
                    
                    if (!empty($fullTranslation) && strlen($fullTranslation) > 50) {
                        return $fullTranslation;
                    }
                }
                
                // Pequena pausa entre tentativas
                usleep(500000); // 0.5 segundos

            } catch (\Exception $e) {
                Log::warning("Método de tradução falhou: " . $e->getMessage());
                continue;
            }
        }

        return null;
    }

    /**
     * Traduz textos longos dividindo em chunks menores
     */
    private function translateLongText($text, $targetLang = 'pt')
    {
        try {
            // Dividir por frases para manter contexto
            $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
            $chunks = [];
            $currentChunk = '';
            
            foreach ($sentences as $sentence) {
                $testChunk = trim($currentChunk . ' ' . $sentence);
                
                if (strlen($testChunk) > 1200) { // Reduzido para 1200 chars
                    if (!empty($currentChunk)) {
                        $chunks[] = trim($currentChunk);
                        $currentChunk = $sentence;
                    } else {
                        // Sentença muito longa, dividir por vírgulas ou palavras
                        $parts = preg_split('/,\s+/', $sentence);
                        if (count($parts) > 1) {
                            foreach ($parts as $part) {
                                if (strlen($currentChunk . $part) > 1200) {
                                    if (!empty($currentChunk)) {
                                        $chunks[] = trim($currentChunk);
                                        $currentChunk = $part;
                                    } else {
                                        $chunks[] = substr($part, 0, 1200);
                                        $currentChunk = substr($part, 1200);
                                    }
                                } else {
                                    $currentChunk .= ' ' . $part;
                                }
                            }
                        } else {
                            // Forçar quebra por palavras
                            $words = explode(' ', $sentence);
                            foreach ($words as $word) {
                                if (strlen($currentChunk . ' ' . $word) > 1200) {
                                    if (!empty($currentChunk)) {
                                        $chunks[] = trim($currentChunk);
                                        $currentChunk = $word;
                                    } else {
                                        $chunks[] = $word;
                                    }
                                } else {
                                    $currentChunk .= ' ' . $word;
                                }
                            }
                        }
                    }
                } else {
                    $currentChunk = $testChunk;
                }
            }
            
            if (!empty($currentChunk)) {
                $chunks[] = trim($currentChunk);
            }
            
            Log::info("Tradução longa: Dividido em " . count($chunks) . " chunks para texto de " . strlen($text) . " chars");
            
            $fullTranslation = '';
            $successfulChunks = 0;
            
            foreach ($chunks as $index => $chunk) {
                if (empty(trim($chunk))) continue;
                
                Log::info("Traduzindo chunk " . ($index + 1) . "/" . count($chunks) . " (" . strlen($chunk) . " chars)");
                
                $chunkTranslation = $this->tryMultipleTranslationMethods($chunk, $targetLang);
                
                if ($chunkTranslation) {
                    $fullTranslation .= $chunkTranslation . ' ';
                    $successfulChunks++;
                } else {
                    Log::warning("Falha ao traduzir chunk " . ($index + 1) . ": " . substr($chunk, 0, 100) . "...");
                    
                    // Se falhar, usar tradução local como fallback
                    $localTranslation = $this->translateLocally($chunk);
                    if ($localTranslation) {
                        $fullTranslation .= $localTranslation . ' ';
                    } else {
                        // Em último caso, manter texto original
                        $fullTranslation .= $chunk . ' ';
                    }
                }
                
                // Pausa entre chunks para não sobrecarregar API
                if ($index < count($chunks) - 1) {
                    usleep(750000); // 0.75 segundos
                }
            }
            
            $result = trim($fullTranslation);
            Log::info("Tradução longa concluída: {$successfulChunks}/" . count($chunks) . " chunks traduzidos com sucesso");
            Log::info("Resultado: " . strlen($text) . " chars original -> " . strlen($result) . " chars traduzido");
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error('Erro na tradução de texto longo: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Tradução local usando palavras-chave
     */
    private function translateLocally($text)
    {
        $translations = [
            // Termos gaming comuns
            'game' => 'jogo',
            'games' => 'jogos',
            'player' => 'jogador',
            'players' => 'jogadores',
            'adventure' => 'aventura',
            'action' => 'ação',
            'strategy' => 'estratégia',
            'simulation' => 'simulação',
            'multiplayer' => 'multijogador',
            'single-player' => 'um jogador',
            'singleplayer' => 'um jogador',
            'story' => 'história',
            'character' => 'personagem',
            'characters' => 'personagens',
            'world' => 'mundo',
            'worlds' => 'mundos',
            'level' => 'nível',
            'levels' => 'níveis',
            'mission' => 'missão',
            'missions' => 'missões',
            'weapon' => 'arma',
            'weapons' => 'armas',
            'skill' => 'habilidade',
            'skills' => 'habilidades',
            'combat' => 'combate',
            'battle' => 'batalha',
            'battles' => 'batalhas',
            'experience' => 'experiência',
            'journey' => 'jornada',
            'challenge' => 'desafio',
            'challenges' => 'desafios',
            'quest' => 'missão',
            'quests' => 'missões',
            'fantasy' => 'fantasia',
            'epic' => 'épico',
            'powerful' => 'poderoso',
            'dangerous' => 'perigoso',
            'mysterious' => 'misterioso',
            'ancient' => 'antigo',
            'modern' => 'moderno',
            'universe' => 'universo',
            'artifact' => 'artefato',
            'enemy' => 'inimigo',
            'enemies' => 'inimigos',
            'boss' => 'chefe',
            'bosses' => 'chefes',
            'defeat' => 'derrotar',
            'destroy' => 'destruir',
            'save' => 'salvar',
            'rescue' => 'resgatar',
            'explore' => 'explorar',
            'discover' => 'descobrir',
            'create' => 'criar',
            'build' => 'construir',
            'survive' => 'sobreviver',
            'fight' => 'lutar',
            'win' => 'vencer',
            'lose' => 'perder',
            
            // Palavras comuns
            'the' => 'o/a',
            'and' => 'e',
            'you' => 'você',
            'your' => 'seu/sua',
            'with' => 'com',
            'from' => 'de',
            'this' => 'este/esta',
            'that' => 'aquele/aquela',
            'will' => 'vai',
            'can' => 'pode',
            'must' => 'deve',
            'have' => 'ter',
            'are' => 'são/está',
            'is' => 'é/está',
            'was' => 'era/estava',
            'were' => 'eram/estavam',
            'be' => 'ser/estar',
            'been' => 'sido/estado',
            'new' => 'novo',
            'old' => 'antigo',
            'first' => 'primeiro',
            'last' => 'último',
            'best' => 'melhor',
            'great' => 'ótimo',
            'good' => 'bom',
            'bad' => 'ruim',
            'big' => 'grande',
            'small' => 'pequeno',
            'fast' => 'rápido',
            'slow' => 'lento',
            'easy' => 'fácil',
            'hard' => 'difícil',
            'fun' => 'divertido',
            'amazing' => 'incrível',
            'awesome' => 'incrível',
            'incredible' => 'incrível',
            'fantastic' => 'fantástico',
            'beautiful' => 'bonito',
            'perfect' => 'perfeito',
            'unique' => 'único',
            'special' => 'especial',
            'different' => 'diferente',
            'same' => 'mesmo',
            'similar' => 'similar',
            'other' => 'outro',
            'another' => 'outro',
            'every' => 'todo',
            'all' => 'todo/tudo',
            'some' => 'alguns',
            'many' => 'muitos',
            'few' => 'poucos',
            'more' => 'mais',
            'less' => 'menos',
            'most' => 'mais',
            'much' => 'muito',
            'very' => 'muito',
            'so' => 'tão',
            'too' => 'também/muito',
            'only' => 'apenas',
            'just' => 'apenas',
            'also' => 'também',
            'even' => 'até mesmo',
            'never' => 'nunca',
            'always' => 'sempre',
            'sometimes' => 'às vezes',
            'often' => 'frequentemente',
            'usually' => 'geralmente',
            'today' => 'hoje',
            'tomorrow' => 'amanhã',
            'yesterday' => 'ontem',
            'now' => 'agora',
            'then' => 'então',
            'here' => 'aqui',
            'there' => 'lá',
            'where' => 'onde',
            'when' => 'quando',
            'why' => 'por que',
            'how' => 'como',
            'what' => 'o que',
            'who' => 'quem',
            'which' => 'qual',
            
            // Frases comuns
            'you are' => 'você é',
            'you will' => 'você vai',
            'you can' => 'você pode',
            'you must' => 'você deve',
            'you have' => 'você tem',
            'you need' => 'você precisa',
            'you want' => 'você quer',
            'you get' => 'você obtém',
            'you see' => 'você vê',
            'you know' => 'você sabe',
            'you think' => 'você pensa',
            'you feel' => 'você sente',
            'you find' => 'você encontra',
            'you take' => 'você pega',
            'you give' => 'você dá',
            'you make' => 'você faz',
            'you do' => 'você faz',
            'you go' => 'você vai',
            'you come' => 'você vem',
            'you play' => 'você joga',
            'take control' => 'assuma o controle',
            'join the' => 'junte-se ao',
            'become the' => 'torne-se o',
            'save the world' => 'salve o mundo',
            'save the' => 'salve o',
            'the best' => 'o melhor',
            'the most' => 'o mais',
            'of all time' => 'de todos os tempos',
            'and more' => 'e muito mais',
            'for the first time' => 'pela primeira vez',
            'once again' => 'mais uma vez',
            'like never before' => 'como nunca antes',
            'in this' => 'neste',
            'on this' => 'neste',
            'at this' => 'neste',
            'to this' => 'para este',
            'from this' => 'deste',
            'with this' => 'com este'
        ];

        $translatedText = $text;
        
        foreach ($translations as $english => $portuguese) {
            // Tradução case-insensitive mantendo a capitalização original
            $pattern = '/\b' . preg_quote($english, '/') . '\b/i';
            $translatedText = preg_replace_callback($pattern, function($matches) use ($portuguese) {
                $original = $matches[0];
                $translation = $portuguese;
                
                // Manter capitalização
                if (ctype_upper($original[0])) {
                    $translation = ucfirst($translation);
                }
                if (ctype_upper($original)) {
                    $translation = strtoupper($translation);
                }
                
                return $translation;
            }, $translatedText);
        }

        return $translatedText;
    }

    /**
     * Traduz a descrição de um jogo e salva na base
     */
    public function translateGameDescription(Game $game, $forceUpdate = false)
    {
        // Verifica se já existe tradução
        if (!$forceUpdate) {
            $existingTranslation = $game->getTranslation('description');
            if ($existingTranslation) {
                return $existingTranslation;
            }
        }

        // Busca descrição da API RAWG se não existe descrição local
        $descriptionToTranslate = $game->description;
        
        if (!$descriptionToTranslate) {
            // Buscar da API RAWG se disponível
            $rawgService = app(\App\Services\RawgService::class);
            $rawgData = $rawgService->getGameDetails($game->title);
            
            if (isset($rawgData['description_raw'])) {
                $descriptionToTranslate = strip_tags($rawgData['description_raw']);
            }
        }

        if (!$descriptionToTranslate || strlen($descriptionToTranslate) < 50) {
            Log::info("TranslateGameDescription: Descrição muito curta ou inexistente para {$game->title}");
            return null;
        }

        Log::info("TranslateGameDescription: Traduzindo {$game->title} - " . strlen($descriptionToTranslate) . " caracteres");

        // Traduz o texto
        $translationResult = $this->translateText($descriptionToTranslate);

        // Verifica se a tradução foi bem-sucedida
        if (!$translationResult['success'] || empty($translationResult['translation'])) {
            Log::warning("TranslateGameDescription: Falha na tradução para {$game->title}: " . ($translationResult['error'] ?? 'Tradução vazia'));
            return null;
        }

        // Log para debug
        Log::info("TranslateGameDescription: {$game->title} - Original: " . strlen($descriptionToTranslate) . " chars, Traduzido: " . strlen($translationResult['translation']) . " chars");

        // Salva na base
        $existingRecord = GameTranslation::where([
            'game_id' => $game->id,
            'field_name' => 'description'
        ])->first();
        
        if ($existingRecord) {
            $existingRecord->update([
                'original_text' => $descriptionToTranslate,
                'translated_text' => $translationResult['translation'], // CORRIGIDO: era 'translated_text'
                'translation_method' => $translationResult['method'],
                'confidence_score' => $translationResult['confidence'],
                'translated_at' => now()
            ]);
            $translation = $existingRecord;
        } else {
            $translation = new GameTranslation();
            $translation->game_id = $game->id;
            $translation->field_name = 'description';
            $translation->original_text = $descriptionToTranslate;
            $translation->translated_text = $translationResult['translation']; // CORRIGIDO: era só 'translation'
            $translation->translation_method = $translationResult['method'];
            $translation->confidence_score = $translationResult['confidence'];
            $translation->translated_at = now();
            $translation->save();
        }

        Log::info("TranslateGameDescription: Tradução salva com sucesso para {$game->title}");
        return $translation;
    }

    /**
     * Traduz descrições de todos os jogos (para comando)
     */
    public function translateAllGameDescriptions($limit = null, $force = false, $recent = false)
    {
        $query = Game::with('translations');
        
        if (!$force) {
            $query->whereDoesntHave('translations', function($q) {
                $q->where('field_name', 'description');
            });
        }
        
        if ($recent) {
            $query->where('created_at', '>=', now()->subDays(7));
        }
            
        if ($limit) {
            $query->limit($limit);
        }

        $games = $query->get();
        $processed = 0;
        $success = 0;

        foreach ($games as $game) {
            try {
                $translation = $this->translateGameDescription($game, $force);
                if ($translation) {
                    $success++;
                }
                $processed++;
                
                // Delay para não sobrecarregar APIs
                usleep(500000); // 0.5 segundos
                
            } catch (\Exception $e) {
                Log::error("Erro ao traduzir jogo {$game->id}: " . $e->getMessage());
                $processed++;
            }
        }

        return [
            'processed' => $processed,
            'success' => $success,
            'total_games' => $games->count()
        ];
    }

    /**
     * Verifica se um texto está em inglês
     */
    public function isEnglishText($text)
    {
        if (empty($text) || strlen(trim($text)) < 10) {
            return false;
        }

        $text = strtolower(trim($text));
        
        // Palavras muito comuns em inglês
        $englishWords = [
            'the', 'and', 'you', 'are', 'for', 'with', 'this', 'that', 'will', 'can', 
            'your', 'from', 'they', 'have', 'been', 'were', 'was', 'not', 'but', 
            'all', 'what', 'would', 'there', 'when', 'more', 'who', 'its', 'now',
            'game', 'play', 'player', 'world', 'character', 'story', 'action',
            'adventure', 'experience', 'battle', 'fight', 'enemy', 'weapons',
            'levels', 'level', 'mission', 'quest', 'explore', 'discover', 'survival'
        ];
        
        // Padrões típicos do inglês
        $englishPatterns = [
            '/\b(a|an|the)\s+\w+/', // artigos ingleses
            '/\b\w+ing\b/',         // gerúndio
            '/\b\w+ed\b/',          // passado
            '/\b(is|are|was|were)\s+/',  // verbo ser/estar
            '/\b(you|your)\s+/',    // pronomes
            '/\b(in|on|at|to|from|with|by)\s+/', // preposições
        ];

        $englishCount = 0;
        $patternCount = 0;
        
        // Contar palavras em inglês
        foreach ($englishWords as $word) {
            if (preg_match('/\b' . preg_quote($word, '/') . '\b/', $text)) {
                $englishCount++;
            }
        }
        
        // Contar padrões típicos do inglês
        foreach ($englishPatterns as $pattern) {
            if (preg_match_all($pattern, $text, $matches)) {
                $patternCount += count($matches[0]);
            }
        }
        
        // Verificar se tem caracteres especiais portugueses
        $hasPortugueseChars = preg_match('/[àáâãçéêíóôõú]/', $text);
        
        // Palavras tipicamente portuguesas
        $portugueseWords = ['você', 'não', 'são', 'está', 'para', 'com', 'uma', 'dos', 'das', 'pelo', 'pela'];
        $portugueseCount = 0;
        
        foreach ($portugueseWords as $word) {
            if (preg_match('/\b' . preg_quote($word, '/') . '\b/', $text)) {
                $portugueseCount++;
            }
        }
        
        // Lógica de decisão mais flexível
        $totalWords = str_word_count($text);
        $englishRatio = ($englishCount + $patternCount) / max($totalWords, 1);
        
        // Log para debug
        \Illuminate\Support\Facades\Log::info("isEnglishText Debug - Palavras inglesas: {$englishCount}, Padrões: {$patternCount}, Portuguesas: {$portugueseCount}, Acentos: " . ($hasPortugueseChars ? 'SIM' : 'NÃO') . ", Ratio: {$englishRatio}");
        
        // Considera inglês se:
        // - Tem pelo menos 2 palavras/padrões em inglês E
        // - Não tem muitas palavras portuguesas E
        // - Não tem acentos portugueses
        return ($englishCount >= 2 || $patternCount >= 2) && 
               $portugueseCount <= 2 && 
               !$hasPortugueseChars &&
               $englishRatio >= 0.1; // pelo menos 10% de conteúdo inglês detectado
    }
}
