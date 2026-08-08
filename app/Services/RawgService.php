<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RawgService
{
    protected ?string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.rawg.key'); // chave no .env (pode ser null)
    }

    public function searchGame(string $title): ?array
    {
        try {
            $response = Http::timeout(5)->get('https://api.rawg.io/api/games', [
                'search' => $title,
                'key' => $this->apiKey,
            ]);

            if ($response->successful()) {
                return $response->json('results')[0] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('Erro na RAWG: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca a melhor capa no formato loja (prioriza Steam header 460×215).
     */
    public function getBestGameImage(string $title): ?string
    {
        try {
            // 1) Steam header — melhor proporção para cards estilo loja
            $steamImage = $this->getSteamImage($title);
            if ($steamImage) {
                return $steamImage;
            }

            // 2) RAWG background
            $response = Http::timeout(5)->get('https://api.rawg.io/api/games', [
                'search' => $title,
                'key' => $this->apiKey,
                'page_size' => 1,
            ]);

            if (! $response->successful()) {
                return $this->getFallbackImage($title);
            }

            $game = $response->json('results.0');
            if (! $game) {
                return $this->getFallbackImage($title);
            }

            if (! empty($game['background_image']) && $this->isGoodImage($game['background_image'])) {
                return $game['background_image'];
            }

            $gameId = $game['id'] ?? null;
            if ($gameId) {
                $detailsResponse = Http::timeout(5)->get("https://api.rawg.io/api/games/{$gameId}", [
                    'key' => $this->apiKey,
                ]);

                if ($detailsResponse->successful()) {
                    $details = $detailsResponse->json();
                    if (! empty($details['background_image']) && $this->isGoodImage($details['background_image'])) {
                        return $details['background_image'];
                    }
                }
            }

            return $this->getFallbackImage($title);
        } catch (\Exception $e) {
            Log::warning('Erro ao buscar imagem do jogo: '.$e->getMessage());

            return $this->getFallbackImage($title);
        }
    }

    /**
     * Verifica se a imagem é de boa qualidade (não é screenshot de gameplay)
     */
    private function isGoodImage(string $url): bool
    {
        // Evitar URLs que claramente são screenshots
        $badPatterns = [
            '/screenshots/',
            '/screenshot/',
            '/gameplay/',
            '/shot/',
            '/clip/',
            '_screenshot',
            'screenshot_'
        ];

        $urlLower = strtolower($url);
        foreach ($badPatterns as $pattern) {
            if (str_contains($urlLower, $pattern)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Header oficial Steam (460×215) — mesmo aspect dos cards da loja.
     */
    private function getSteamImage(string $title): ?string
    {
        try {
            $response = Http::timeout(3)->get('https://store.steampowered.com/api/storesearch/', [
                'term' => $title,
                'l' => 'english',
                'cc' => 'US',
            ]);

            if (! $response->successful()) {
                return null;
            }

            $item = $response->json('items.0');
            $appId = $item['id'] ?? null;
            if (! $appId) {
                return null;
            }

            $url = \App\Models\Game::steamHeaderUrl((int) $appId);

            // Confirma que o header existe (evita appId errado / 404)
            $check = Http::timeout(3)->head($url);
            if ($check->successful()) {
                return $url;
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Busca detalhes completos de um jogo (cache 12h; falhas 15min).
     */
    public function getGameDetails(string $title): ?array
    {
        $cacheKey = 'rawg:game-details:'.Str::slug(mb_strtolower(trim($title)));
        $cached = Cache::get($cacheKey);

        if ($cached === false) {
            return null;
        }

        if (is_array($cached)) {
            return $cached;
        }

        $details = $this->fetchGameDetails($title);
        Cache::put(
            $cacheKey,
            $details ?? false,
            $details ? now()->addHours(12) : now()->addMinutes(15)
        );

        return $details;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchGameDetails(string $title): ?array
    {
        try {
            $response = Http::timeout(3)->get('https://api.rawg.io/api/games', [
                'search' => $title,
                'key' => $this->apiKey,
                'page_size' => 1,
            ]);

            if (! $response->successful()) {
                Log::warning('RawgService: Erro na busca inicial - Status: '.$response->status());

                return null;
            }

            $game = $response->json('results.0');
            if (! $game) {
                return null;
            }

            $gameId = $game['id'];
            $detailsResponse = Http::timeout(3)->get("https://api.rawg.io/api/games/{$gameId}", [
                'key' => $this->apiKey,
            ]);

            if (! $detailsResponse->successful()) {
                Log::warning('RawgService: Erro ao buscar detalhes - Status: '.$detailsResponse->status());

                return null;
            }

            $details = $detailsResponse->json();

            if (empty($details['description_raw']) && ! empty($details['description'])) {
                $details['description_raw'] = strip_tags($details['description']);
            }

            $screenshotsResponse = Http::timeout(5)->get("https://api.rawg.io/api/games/{$gameId}/screenshots", [
                'key' => $this->apiKey,
                'page_size' => 6,
            ]);

            if ($screenshotsResponse->successful()) {
                $details['screenshots'] = $screenshotsResponse->json('results') ?? [];
            }

            return $details;
        } catch (\Exception $e) {
            Log::error('RawgService: Erro ao buscar detalhes do jogo: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Sem placeholder externo (via.placeholder.com costuma falhar).
     * O accessor do Game trata ausência de imagem na UI.
     */
    private function getFallbackImage(string $title): ?string
    {
        return null;
    }

    /**
     * Lista jogos free-to-play (gratuitos permanentes) da RAWG.
     * Retorna array de jogos com id, name, slug, background_image, etc.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getFreeToPlayGames(int $pageSize = 20, int $page = 1): array
    {
        try {
            $response = Http::timeout(15)->get('https://api.rawg.io/api/games', [
                'key' => $this->apiKey,
                'tags' => 'free-to-play',
                'page_size' => min($pageSize, 40),
                'page' => $page,
                'ordering' => '-rating',
            ]);

            if (!$response->successful()) {
                Log::warning('RawgService getFreeToPlayGames: HTTP ' . $response->status());
                return [];
            }

            $data = $response->json();
            return $data['results'] ?? [];
        } catch (\Exception $e) {
            Log::warning('RawgService getFreeToPlayGames: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna a URL da loja (Steam, etc.) para um jogo RAWG, se disponível.
     */
    public function getGameStoreUrl(int $rawgGameId): ?string
    {
        try {
            $response = Http::timeout(5)->get("https://api.rawg.io/api/games/{$rawgGameId}/stores", [
                'key' => $this->apiKey,
            ]);
            if (!$response->successful()) {
                return null;
            }
            $results = $response->json('results');
            if (empty($results)) {
                return null;
            }
            // Priorizar Steam (store_id 1), depois primeira loja disponível
            foreach ($results as $item) {
                if (($item['store_id'] ?? 0) === 1 && !empty($item['url'])) {
                    return $item['url'];
                }
            }
            return $results[0]['url'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Busca descrição de fontes alternativas quando RAWG não retorna
     */
    public function getGameDescriptionFromAlternativeSources(string $title): ?string
    {
        try {
            Log::info("RawgService: Buscando descrição alternativa para: {$title}");
            
            // Fonte 1: Steam Store API (não oficial, mas funcional)
            $steamDesc = $this->getSteamGameDescription($title);
            if ($steamDesc) {
                Log::info("RawgService: Descrição encontrada no Steam");
                return $steamDesc;
            }
            
            // Fonte 2: Usar dados básicos do próprio RAWG (às vezes tem info no campo 'released', 'genres', etc)
            $basicInfo = $this->getBasicGameInfo($title);
            if ($basicInfo) {
                Log::info("RawgService: Descrição básica gerada a partir de metadados");
                return $basicInfo;
            }
            
            Log::warning("RawgService: Nenhuma descrição encontrada em fontes alternativas");
            return null;
            
        } catch (\Exception $e) {
            Log::error("RawgService: Erro ao buscar fontes alternativas: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca descrição no Steam (método não oficial)
     */
    private function getSteamGameDescription(string $title): ?string
    {
        try {
            // Steam store search (não é API oficial, mas funciona)
            $response = Http::timeout(3)->get('https://store.steampowered.com/api/storesearch/', [
                'term' => $title,
                'l' => 'english',
                'cc' => 'US'
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                $items = $data['items'] ?? [];
                
                if (!empty($items)) {
                    $steamId = $items[0]['id'];
                    
                    // Buscar detalhes do app
                    $detailResponse = Http::timeout(3)->get('https://store.steampowered.com/api/appdetails/', [
                        'appids' => $steamId,
                        'l' => 'english'
                    ]);
                    
                    if ($detailResponse->successful()) {
                        $detailData = $detailResponse->json();
                        $appData = $detailData[$steamId]['data'] ?? null;
                        
                        if ($appData && isset($appData['detailed_description'])) {
                            return strip_tags($appData['detailed_description']);
                        } elseif ($appData && isset($appData['short_description'])) {
                            return strip_tags($appData['short_description']);
                        }
                    }
                }
            }
            
            return null;
        } catch (\Exception $e) {
            Log::warning("RawgService: Erro ao buscar no Steam: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Gera descrição básica a partir de metadados do jogo
     */
    private function getBasicGameInfo(string $title): ?string
    {
        try {
            $response = Http::timeout(3)->get('https://api.rawg.io/api/games', [
                'search' => $title,
                'key' => $this->apiKey,
                'page_size' => 1
            ]);

            if ($response->successful()) {
                $game = $response->json('results')[0] ?? null;
                
                if ($game) {
                    $description = "";
                    
                    // Usar gêneros
                    if (!empty($game['genres'])) {
                        $genres = array_map(fn($g) => $g['name'], array_slice($game['genres'], 0, 3));
                        $description .= "This is a " . implode(', ', $genres) . " game";
                    }
                    
                    // Adicionar ano de lançamento
                    if (!empty($game['released'])) {
                        $year = date('Y', strtotime($game['released']));
                        $description .= " released in {$year}";
                    }
                    
                    // Adicionar plataformas
                    if (!empty($game['platforms'])) {
                        $platforms = array_map(fn($p) => $p['platform']['name'], array_slice($game['platforms'], 0, 3));
                        $description .= ". Available on " . implode(', ', $platforms);
                    }
                    
                    // Adicionar rating se disponível
                    if (!empty($game['rating'])) {
                        $description .= ". Player rating: {$game['rating']}/5";
                    }
                    
                    $description .= ".";
                    
                    return strlen($description) > 50 ? $description : null;
                }
            }
            
            return null;
        } catch (\Exception $e) {
            Log::warning("RawgService: Erro ao buscar info básica: " . $e->getMessage());
            return null;
        }
    }
}
