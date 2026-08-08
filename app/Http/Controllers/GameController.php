<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Game;
use App\Services\RawgService;
use App\Services\SteamStoreService;
use App\Support\GameCatalogQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GameController extends Controller
{
    public function index(Request $request)
    {
        $query = Game::with('categories')->valid();
        GameCatalogQuery::apply($query, $request);

        $games = $query->paginate(20)->appends($request->only(GameCatalogQuery::filterKeys()));
        $categories = Category::orderBy('name')->get();
        $stores = Game::valid()->distinct()->pluck('store_name')->filter()->sort()->values();

        return view('games.index', compact('games', 'categories', 'stores'));
    }

    public function show(Game $game, SteamStoreService $steam, RawgService $rawg)
    {
        $game->load(['categories', 'translations']);

        // Steam primeiro (funciona na VPS + PT-BR); RAWG como fallback
        $rawgData = $steam->getCatalogDetails($game);
        if (! $rawgData) {
            $rawgData = $rawg->getGameDetails($game->title);
        }

        $this->hydrateGameFromCatalog($game, $rawgData);
        $game->load('categories');

        $relatedOffers = Game::with(['categories', 'translations'])->valid()
            ->where('id', '!=', $game->id)
            ->where(function ($q) use ($game) {
                $q->where('title', $game->title)
                    ->orWhere('title', 'LIKE', $game->title.'%');
            })
            ->orderBy('sale_price')
            ->take(5)
            ->get();

        $similarGames = $this->findSimilarGames($game, $rawgData);

        return view('games.show', compact('game', 'rawgData', 'relatedOffers', 'similarGames'));
    }

    public function offers(Game $game)
    {
        $offers = Game::with('categories')->valid()->where('title', 'LIKE', '%'.$game->title.'%')
            ->where('id', '!=', $game->id)
            ->orderByDesc('discount_percent')
            ->get();

        if ($offers->isEmpty()) {
            $categoryIds = $game->categories->pluck('id');
            $offers = Game::with('categories')->valid()->whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('category_id', $categoryIds);
            })
                ->where('id', '!=', $game->id)
                ->orderByDesc('discount_percent')
                ->limit(10)
                ->get();
        }

        return view('games.offers', compact('game', 'offers'));
    }

    public function free(Request $request)
    {
        $query = Game::with('categories')->activeFree();
        GameCatalogQuery::apply($query, $request, allowFreeScope: false);
        if (! $request->filled('sort')) {
            $query->reorder()->orderByDesc('free_since');
        }

        $games = $query->paginate(20)->appends($request->only(GameCatalogQuery::filterKeys()));
        $categories = Category::orderBy('name')->get();
        $stores = Game::activeFree()->distinct()->pluck('store_name')->filter()->sort()->values();

        return view('games.free', compact('games', 'categories', 'stores'));
    }

    /**
     * @param  array<string, mixed>|null  $catalog
     */
    protected function hydrateGameFromCatalog(Game $game, ?array $catalog): void
    {
        if (! $catalog) {
            return;
        }

        $dirty = false;

        $isGenericDescription = blank($game->description)
            || str_starts_with((string) $game->description, 'Oferta encontrada');

        if ($isGenericDescription && ! empty($catalog['description_raw'])) {
            $game->description = Str::limit($catalog['description_raw'], 5000, '');
            $dirty = true;
        }

        if (blank($game->developer) && ! empty($catalog['developers'][0]['name'])) {
            $game->developer = $catalog['developers'][0]['name'];
            $dirty = true;
        }

        if (blank($game->metacritic) && ! empty($catalog['metacritic'])) {
            $game->metacritic = (int) $catalog['metacritic'];
            $dirty = true;
        }

        if (blank($game->release_date) && ! empty($catalog['released'])) {
            try {
                $game->release_date = \Carbon\Carbon::parse($catalog['released']);
                $dirty = true;
            } catch (\Throwable) {
                // datas Steam em PT ("8/nov./2022") às vezes falham — ignora
            }
        }

        if ($dirty) {
            $game->save();
        }

        // Se a capa for placeholder / sem Steam, promove header oficial quando achar AppID
        if (! empty($catalog['background_image']) && ! empty($catalog['steam_app_id'])) {
            $img = (string) $game->image;
            $needsCover = $img === ''
                || str_contains($img, 'placeholder')
                || str_contains($img, 'placehold.co')
                || ! preg_match('#/(?:steam/)?apps/\d+/#i', $img);

            if ($needsCover) {
                $game->image = Game::steamHeaderUrl((int) $catalog['steam_app_id']);
                $game->save();
            }
        }

        // Associa gêneros do catálogo às categorias locais quando o jogo ainda não tem
        if ($game->categories->isEmpty() && ! empty($catalog['genres'])) {
            $ids = [];
            foreach ($catalog['genres'] as $genre) {
                $name = trim((string) ($genre['name'] ?? ''));
                if ($name === '') {
                    continue;
                }
                $category = Category::firstOrCreate(
                    ['slug' => Str::slug($name)],
                    ['name' => $name]
                );
                $ids[] = $category->id;
            }
            if ($ids !== []) {
                $game->categories()->syncWithoutDetaching($ids);
            }
        }
    }

    /**
     * @param  array<string, mixed>|null  $catalog
     * @return \Illuminate\Support\Collection<int, Game>
     */
    protected function findSimilarGames(Game $game, ?array $catalog)
    {
        $categoryIds = $game->categories->pluck('id');

        if ($categoryIds->isEmpty() && ! empty($catalog['genres'])) {
            $names = collect($catalog['genres'])->pluck('name')->filter()->values();
            $categoryIds = Category::query()
                ->whereIn('name', $names)
                ->orWhereIn('slug', $names->map(fn ($n) => Str::slug($n)))
                ->pluck('id');
        }

        if ($categoryIds->isNotEmpty()) {
            return Game::with(['categories', 'translations'])
                ->valid()
                ->where('id', '!=', $game->id)
                ->whereHas('categories', function ($q) use ($categoryIds) {
                    $q->whereIn('categories.id', $categoryIds);
                })
                ->withCount([
                    'categories as shared_categories_count' => function ($q) use ($categoryIds) {
                        $q->whereIn('categories.id', $categoryIds);
                    },
                ])
                ->orderByDesc('shared_categories_count')
                ->orderByDesc('discount_percent')
                ->orderBy('sale_price')
                ->take(8)
                ->get();
        }

        // Fallback: mesma loja, descontos altos (evita lista aleatória)
        return Game::with(['categories', 'translations'])
            ->valid()
            ->where('id', '!=', $game->id)
            ->when($game->store_name, fn ($q) => $q->where('store_name', $game->store_name))
            ->orderByDesc('discount_percent')
            ->take(8)
            ->get();
    }
}
