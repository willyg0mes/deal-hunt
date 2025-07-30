<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Category;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index(Request $request)
    {
        $query = Game::with('categories')->valid();
        
        // Filtro por categoria
        if ($request->has('category') && $request->category) {
            $query->whereHas('categories', function($q) use ($request) {
                $q->where('slug', $request->category)
                  ->orWhere('name', $request->category);
            });
        }
        
        // Filtro por loja
        if ($request->has('store') && $request->store) {
            $query->where('store_name', $request->store);
        }
        
        // Busca por termo
        if ($request->has('search') && $request->search) {
            $query->where('title', 'LIKE', '%' . $request->search . '%');
        }
        
        $games = $query->orderByDesc('discount_percent')->paginate(20);
        $categories = Category::orderBy('name')->get();
        $stores = Game::valid()->distinct()->pluck('store_name')->filter()->sort();
        
        return view('games.index', compact('games', 'categories', 'stores'));
    }

    public function show(Game $game)
    {
        $game->load('categories');
        
        // Buscar dados adicionais do RAWG API
        $rawgService = app(\App\Services\RawgService::class);
        $rawgData = $rawgService->getGameDetails($game->title);
        
        // Buscar ofertas relacionadas (mesmo jogo em outras lojas)
        $relatedOffers = Game::valid()
            ->where('title', 'LIKE', '%' . $game->title . '%')
            ->where('id', '!=', $game->id)
            ->orderBy('sale_price')
            ->take(5)
            ->get();
        
        // Jogos similares (mesma categoria ou desenvolvedor)
        $similarGames = Game::valid()
            ->where('id', '!=', $game->id)
            ->where(function($query) use ($game) {
                if ($game->developer) {
                    $query->where('developer', $game->developer);
                }
                if ($game->categories->isNotEmpty()) {
                    $query->orWhereHas('categories', function($q) use ($game) {
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
        // Busca outras ofertas do mesmo jogo em lojas diferentes
        $offers = Game::with('categories')->valid()->where('title', 'LIKE', '%' . $game->title . '%')
            ->where('id', '!=', $game->id)
            ->orderByDesc('discount_percent')
            ->get();

        // Se não encontrar ofertas do mesmo jogo, busca jogos da mesma categoria
        if ($offers->isEmpty()) {
            $categoryIds = $game->categories->pluck('id');
            $offers = Game::with('categories')->valid()->whereHas('categories', function($q) use ($categoryIds) {
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
        // Usar a nova lógica de jogos gratuitos permanentes
        $query = Game::with('categories')->activeFree();
        
        // Filtro por categoria
        if ($request->has('category') && $request->category) {
            $query->whereHas('categories', function($q) use ($request) {
                $q->where('slug', $request->category)
                  ->orWhere('name', $request->category);
            });
        }
        
        // Filtro por loja
        if ($request->has('store') && $request->store) {
            $query->where('store_name', $request->store);
        }
        
        // Busca por termo
        if ($request->has('search') && $request->search) {
            $query->where('title', 'LIKE', '%' . $request->search . '%');
        }
        
        $games = $query->orderBy('free_since', 'desc')->paginate(20);
        $categories = Category::orderBy('name')->get();
        $stores = Game::activeFree()->distinct()->pluck('store_name')->filter()->sort();
        
        return view('games.free', compact('games', 'categories', 'stores'));
    }
}
