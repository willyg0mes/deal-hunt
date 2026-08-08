<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Game;
use App\Models\Category;
use App\Services\RawgService;

class FetchFreeGamesCommand extends Command
{
    protected $signature = 'games:fetch-free {--limit=80 : Número de jogos gratuitos a buscar (padrão 80)}';
    protected $description = 'Busca jogos gratuitos permanentes (Free to Play) na RAWG e adiciona ao catálogo';

    public function __construct(protected RawgService $rawgService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        if ($limit < 1 || $limit > 200) {
            $this->error('Use --limit entre 1 e 200.');
            return 1;
        }

        $this->info("🆓 Buscando até {$limit} jogos gratuitos permanentes na RAWG (tag: free-to-play)...");

        if (empty(config('services.rawg.key'))) {
            $this->error('RAWG_API_KEY não está definida no .env. Configure para usar a RAWG.');
            return 1;
        }

        $pageSize = 20;
        $processed = 0;
        $collected = 0;
        $page = 1;

        while ($collected < $limit) {
            $toFetch = min($pageSize, $limit - $collected);
            $freeGames = $this->rawgService->getFreeToPlayGames($toFetch, $page);

            if (empty($freeGames)) {
                $this->warn("Página {$page}: RAWG não retornou jogos. Verifique RAWG_API_KEY e conexão.");
                if ($page === 1) {
                    $this->error('Nenhum resultado na primeira página. Abortando.');
                    return 1;
                }
                break;
            }

            $this->info("📥 Página {$page}: " . count($freeGames) . " jogos recebidos.");

            foreach ($freeGames as $fg) {
                if ($collected >= $limit) {
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
                        $processed++;
                    }
                    $collected++;
                    continue;
                }
                $image = $fg['background_image'] ?? null;
                $slug = $fg['slug'] ?? null;
                $rawgId = $fg['id'] ?? null;
                $storeUrl = null;
                if ($rawgId) {
                    usleep(200000);
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
                $processed++;
                $collected++;
            }
            $page++;
            if (count($freeGames) < $pageSize) {
                break;
            }
            sleep(1);
        }

        $this->table(['Estatística', 'Valor'], [
            ['Jogos gratuitos processados', $processed],
            ['Total de jogos gratuitos no site', Game::free()->count()],
            ['Total de jogos no catálogo', Game::count()],
        ]);
        $this->info('✅ Busca de jogos gratuitos concluída.');
        return 0;
    }

    private function associateCategories(Game $game): void
    {
        static $categoriesCache = null;
        if ($categoriesCache === null) {
            $categoriesCache = Category::pluck('id', 'slug')->toArray();
        }
        $title = strtolower($game->title);
        $categories = [];
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
        if (empty($categories) && isset($categoriesCache['indie'])) {
            $categories[] = $categoriesCache['indie'];
        }
        if (!empty($categories)) {
            $game->categories()->sync(array_unique($categories));
        }
    }
}
