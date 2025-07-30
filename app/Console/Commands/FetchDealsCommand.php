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
    protected $signature = 'games:fetch-deals {--limit=100 : Number of deals to fetch (default: 100, max: 3000)}';
    protected $description = 'Fetch game deals from CheapShark API with improved images';

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
                
                // Buscar deals da API CheapShark
                $response = Http::timeout(30)->get('https://www.cheapshark.com/api/1.0/deals', [
                    'storeID' => '1,7,8,11,13,15,25,27,28,30,31,33,34,35', // Principais lojas
                    'upperPrice' => 200,
                    'pageSize' => $currentPageSize,
                    'pageNumber' => $page,
                    'sortBy' => 'Deal Rating'
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

                // Verificar se o jogo já existe
                $existingGame = Game::where('title', $deal['title'])->first();
                
                if ($existingGame) {
                    // Atualizar preços se mudaram significativamente (diferença > 1%)
                    $priceDiff = abs($existingGame->sale_price - (float)$deal['salePrice']);
                    if ($priceDiff > ($existingGame->sale_price * 0.01)) {
                        $existingGame->update([
                            'normal_price' => (float)$deal['normalPrice'],
                            'sale_price' => (float)$deal['salePrice'],
                            'discount_percent' => round((float)$deal['savings']),
                            'updated_at' => now()
                        ]);
                        $updated++;
                    }
                    continue;
                }

                // Buscar imagem melhorada via RawgService (com cache para evitar muitas chamadas)
                $cacheKey = 'rawg_image_' . md5($deal['title']);
                $bestImage = cache()->remember($cacheKey, 3600, function() use ($deal) {
                    return $this->rawgService->getBestGameImage($deal['title']);
                });

                // Mapear store ID para nome
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
                    '35' => 'NOCTRE'
                ];

                $storeName = $storeMapping[$deal['storeID']] ?? 'Desconhecida';

                // Criar novo jogo
                $game = Game::create([
                    'title' => $deal['title'],
                    'image' => $bestImage ?: "https://www.cheapshark.com{$deal['thumb']}",
                    'store' => $deal['storeID'],
                    'store_name' => $storeName,
                    'normal_price' => (float)$deal['normalPrice'],
                    'sale_price' => (float)$deal['salePrice'],
                    'discount_percent' => round((float)$deal['savings']),
                    'deal_url' => "https://www.cheapshark.com/redirect?dealID={$deal['dealID']}",
                    'deal_ends_at' => now()->addDays(7), // 7 dias de validade
                    'description' => "Oferta encontrada na {$storeName}",
                    'is_active' => true,
                ]);

                // Associar categorias baseadas no título
                $this->associateCategories($game);

                $processed++;
            }

            $bar->finish();
            $this->newLine();

            $this->table(['Estatística', 'Quantidade'], [
                ['Jogos processados', $processed],
                ['Jogos atualizados', $updated],
                ['Total de jogos', Game::count()],
            ]);

            $this->info('✅ Busca de deals concluída com sucesso!');
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
