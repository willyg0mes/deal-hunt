<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Game;
use App\Support\GameCatalogQuery;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $query = Game::with('categories')->valid();
        GameCatalogQuery::apply($query, $request);

        $games = $query->paginate(20)->appends($request->only(GameCatalogQuery::filterKeys()));

        $storeNames = Game::valid()
            ->distinct()
            ->whereNotNull('store_name')
            ->where('store_name', '!=', '')
            ->pluck('store_name')
            ->sort()
            ->values();

        $topGames = Game::valid()
            ->where('discount_percent', '>', 0)
            ->orderByDesc('discount_percent')
            ->orderBy('sale_price')
            ->take(15)
            ->get();
        $categories = Category::orderBy('name')->get();

        if ($request->ajax()) {
            return view('partials.games-list', compact('games'))->render();
        }

        return view('home', compact('games', 'storeNames', 'topGames', 'categories'));
    }
}
