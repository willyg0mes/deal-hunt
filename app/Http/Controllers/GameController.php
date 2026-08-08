<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Game;
use App\Support\GameCatalogQuery;
use Illuminate\Http\Request;

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

    public function show(Game $game)
    {
        $game->load(['categories', 'translations']);

        $rawgService = app(\App\Services\RawgService::class);
        $rawgData = $rawgService->getGameDetails($game->title);

        $relatedOffers = Game::with(['categories', 'translations'])->valid()
            ->where('title', 'LIKE', '%'.$game->title.'%')
            ->where('id', '!=', $game->id)
            ->orderBy('sale_price')
            ->take(5)
            ->get();

        $similarGames = Game::with(['categories', 'translations'])->valid()
            ->where('id', '!=', $game->id)
            ->where(function ($query) use ($game) {
                if ($game->developer) {
                    $query->where('developer', $game->developer);
                }
                if ($game->categories->isNotEmpty()) {
                    $query->orWhereHas('categories', function ($q) use ($game) {
                        $q->whereIn('categories.id', $game->categories->pluck('id'));
                    });
                }
            })
            ->orderByDesc('metacritic')
            ->take(6)
            ->get();

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
        // Na página de grátis, ordenação padrão por free_since se sort não veio
        if (! $request->filled('sort')) {
            $query->reorder()->orderByDesc('free_since');
        }

        $games = $query->paginate(20)->appends($request->only(GameCatalogQuery::filterKeys()));
        $categories = Category::orderBy('name')->get();
        $stores = Game::activeFree()->distinct()->pluck('store_name')->filter()->sort()->values();

        return view('games.free', compact('games', 'categories', 'stores'));
    }
}
