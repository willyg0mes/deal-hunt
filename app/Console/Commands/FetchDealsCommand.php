<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Game;
use App\Models\Category;
use App\Services\RawgService;
use Illuminate\Support\Str;

class FetchDealsCommand extends Command
{
    protected $signature = 'games:fetch-deals 
                            {--limit=100 : Number of deals to fetch (default: 100, max: 3000)} 
                            {--free=30 : Number of permanently free games to fetch from RAWG (0 = skip)}';
    protected $description = 'Fetch game deals from CheapShark API and optionally free-to-play games from RAWG';

    public function __construct(protected RawgService $rawgService)
    {
        parent::__construct();
    }

    public function handle()
    {
        $limit = $this->option('limit');
        $this->info("🎮 Buscando {$limit} ofertas da CheapShark API...");

        try {
            $allDeals = [];
            $pageSize = 60; // Limite máximo efetivo da API CheapShark
            $totalPages = ceil($limit / $pageSize);
            
            $this->info("📄 Fazendo {$totalPages} requisições para buscar {$limit} deals...");

            for ($page = 0; $page < $totalPages; $page++) {
                $currentPageSize = min($pageSize, $limit - count($allDeals));
                
                if ($currentPageSize <= 0) break;
                
                $this->info("📥 Buscando página " . ($page + 1) . "/{$totalPages} ({$currentPageSize} deals)...");
                
                // Buscar deals da API CheapShark (exige User-Agent descritivo)
                $response = \App\Support\CheapShark::http(30)->get('https://www.cheapshark.com/api/1.0/deals', [
                    'storeID' => '1,7,8,11,13,15,25,27,28,30,31,33,34,35', // Principais lojas
                    'upperPrice' => 200,
                    'pageSize' => $currentPageSize,
                    'pageNumber' => $page,
                    'sortBy' => 'Deal Rating',
                    'onSale' => 1,
                ]);

                if (!$response->successful()) {
                    $this->error("Falha ao buscar página " . ($page + 1) . " da API CheapShark");
                    continue;
                }

                $pageDeals = $response->json();
                
                if (empty($pageDeals)) {
                    $this->warn("Página " . ($page + 1) . " retornou vazia. Parando busca.");
                    break;
                }
                
                $allDeals = array_merge($allDeals, $pageDeals);
                $this->info("✅ " . count($pageDeals) . " deals adicionados. Total: " . count($allDeals));
                
                // Pausa entre requisições para evitar rate limiting
                if ($page < $totalPages - 1) {
                    sleep(1);
                }
            }

            $deals = $allDeals;
            $this->info("🎯 Total de " . count($deals) . " deals coletados. Processando...");

            $bar = $this->output->createProgressBar(count($deals));
            $bar->start();

            $processed = 0;
            $updated = 0;

            foreach ($deals as $deal) {
                $bar->advance();

                // Preferir dealID; cair para título+loja / título
                $existingGame = Game::where('external_id', $deal['dealID'])->first()
                    ?? Game::where('title', $deal['title'])->where('store', (string) $deal['storeID'])->first()
                    ?? Game::where('title', $deal['title'])->first();

                $storeMapping = [
                    '1' => 'Steam',
                    '7' => 'GOG',
                    '8' => 'Origin',
                    '11' => 'Humble Store',
                    '13' => 'Uplay',
                    '15' => 'Fanatical',
                    '25' => 'Epic Games',
                    '27' => 'GamesPlanet',
                    '28' => 'Gamesload',
                    '30' => 'GamersGate',
                    '31' => 'Games Republic',
                    '33' => 'GameBillet',
                    '34' => 'Voidu',
                    '35' => 'NOCTRE',
                ];

                $storeName = $storeMapping[$deal['storeID']] ?? 'Desconhecida';
                $dealUrl = 'https://www.cheapshark.com/redirect?dealID='.$deal['dealID'];
                $payload = [
                    'title' => $deal['title'],
                    'store' => $deal['storeID'],
                    'store_name' => $storeName,
                    'normal_price' => (float) $deal['normalPrice'],
                    'sale_price' => (float) $deal['salePrice'],
                    'discount_percent' => round((float) $deal['savings']),
                    'deal_url' => $dealUrl,
                    'deal_ends_at' => now()->addDays(7),
                    'external_id' => $deal['dealID'],
                    'is_active' => true,
                ];

                if ($existingGame) {
                    // Sempre renova validade/preço — deals expirados voltam ao catálogo
                    if (empty($existingGame->image) || str_contains((string) $existingGame->image, 'placeholder')) {
                        $cacheKey = 'rawg_image_'.md5($deal['title']);
                        $bestImage = cache()->remember($cacheKey, 3600, function () use ($deal) {
                            return $this->rawgService->getBestGameImage($deal['title']);
                        });
                        $payload['image'] = $bestImage ?: \App\Support\CheapShark::thumbUrl($deal['thumb'] ?? null);
                    }

                    $existingGame->update($payload);
                    $updated++;
                    continue;
                }

                // Buscar imagem melhorada via RawgService (com cache para evitar muitas chamadas)
                $cacheKey = 'rawg_image_'.md5($deal['title']);
                $bestImage = cache()->remember($cacheKey, 3600, function () use ($deal) {
                    return $this->rawgService->getBestGameImage($deal['title']);
                });

                // Criar novo jogo
                $game = Game::create(array_merge($payload, [
                    'image' => $bestImage ?: \App\Support\CheapShark::thumbUrl($deal['thumb'] ?? null),
                    'description' => "Oferta encontrada na {$storeName}",
                ]));

                // Associar categorias baseadas no título
                $this->associateCategories($game);

                $processed++;
            }

            $bar->finish();
            $this->newLine();

            $freeProcessed = 0;
            $freeLimit = (int) $this->option('free');
            if ($freeLimit > 0) {
                $this->info("🆓 Buscando até {$freeLimit} jogos gratuitos permanentes (RAWG)...");
                $pageSize = 20;
                $freeCollected = 0;
                $page = 1;
                while ($freeCollected < $freeLimit) {
                    $toFetch = min($pageSize, $freeLimit - $freeCollected);
                    $freeGames = $this->rawgService->getFreeToPlayGames($toFetch, $page);
                    if (empty($freeGames)) {
                        break;
                    }
                    foreach ($freeGames as $fg) {
                        if ($freeCollected >= $freeLimit) {
                            break;
                        }
                        $title = $fg['name'] ?? '';
                        if (empty($title)) {
                            continue;
                        }
                        $existing = Game::where('title', $title)->first();
                        if ($existing) {
                            if (!$existing->is_free_permanently) {
                                $existing->update([
                                    'is_free_permanently' => true,
                                    'free_since' => $existing->free_since ?? now(),
                                    'normal_price' => 0,
                                    'sale_price' => 0,
                                    'discount_percent' => 0,
                                    'deal_ends_at' => null,
                                    'updated_at' => now(),
                                ]);
                                $freeProcessed++;
                            }
                            $freeCollected++;
                            continue;
                        }
                        $image = $fg['background_image'] ?? null;
                        $slug = $fg['slug'] ?? null;
                        $rawgId = $fg['id'] ?? null;
                        $storeUrl = null;
                        if ($rawgId) {
                            usleep(300000); // 0.3s entre chamadas à API de lojas
                            $storeUrl = $this->rawgService->getGameStoreUrl($rawgId);
                        }
                        if (!$storeUrl && $slug) {
                            $storeUrl = "https://rawg.io/games/{$slug}";
                        }
                        $game = Game::create([
                            'title' => $title,
                            'image' => $image ?? 'https://via.placeholder.com/460x215/1a1a1a/ffffff?text=' . urlencode($title),
                            'store' => 0,
                            'store_name' => 'Free to Play',
                            'normal_price' => 0,
                            'sale_price' => 0,
                            'discount_percent' => 0,
                            'deal_url' => $storeUrl,
                            'deal_ends_at' => null,
                            'description' => 'Jogo gratuito permanente (Free to Play).',
                            'is_active' => true,
                            'is_free_permanently' => true,
                            'free_since' => now(),
                        ]);
                        $this->associateCategories($game);
                        $freeProcessed++;
                        $freeCollected++;
                    }
                    $page++;
                    if (count($freeGames) < $pageSize) {
                        break;
                    }
                    sleep(1);
                }
                $this->info("✅ {$freeProcessed} jogos gratuitos processados.");
            }

            $this->table(['Estatística', 'Quantidade'], [
                ['Deals processados', $processed],
                ['Deals atualizados', $updated],
                ['Jogos gratuitos (novos/atualizados)', $freeProcessed],
                ['Total de jogos', Game::count()],
            ]);

            $this->info('✅ Busca de deals e jogos gratuitos concluída com sucesso!');
            return 0;

        } catch (\Exception $e) {
            $this->error("Erro ao buscar deals: {$e->getMessage()}");
            return 1;
        }
    }

    private function associateCategories(Game $game)
    {
        // Cache das categorias para evitar múltiplas consultas
        static $categoriesCache = null;
        if ($categoriesCache === null) {
            $categoriesCache = Category::pluck('id', 'slug')->toArray();
        }
        
        $title = strtolower($game->title);
        $categories = [];

        // Mapear palavras-chave para categorias
        $categoryMap = [
            'rpg' => ['rpg', 'role', 'fantasy', 'adventure'],
            'acao' => ['action', 'shooter', 'fight', 'combat', 'war'],
            'terror' => ['horror', 'scary', 'dead', 'zombie', 'survival'],
            'corrida' => ['racing', 'car', 'drive', 'speed'],
            'esportes' => ['sport', 'football', 'soccer', 'basketball', 'hockey'],
            'estrategia' => ['strategy', 'civilization', 'empire', 'command'],
            'simulacao' => ['simulation', 'sim', 'city', 'farm', 'truck'],
        ];

        foreach ($categoryMap as $categorySlug => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($title, $keyword)) {
                    if (isset($categoriesCache[$categorySlug])) {
                        $categories[] = $categoriesCache[$categorySlug];
                    }
                    break;
                }
            }
        }

        // Se não encontrou categorias específicas, adicionar "Indie" como padrão
        if (empty($categories) && isset($categoriesCache['indie'])) {
            $categories[] = $categoriesCache['indie'];
        }

        if (!empty($categories)) {
            $game->categories()->sync(array_unique($categories));
        }
    }
}
