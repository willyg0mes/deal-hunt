<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $query = Game::with('categories')->valid(); // Usar scope válidos (ativos e não expirados)

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        // Filtro por loja
        if ($request->filled('store')) {
            $query->where('store_name', $request->store);
        }
        if ($request->filled('min_discount')) {
            $query->where('discount_percent', '>=', $request->min_discount);
        }
        if ($request->filled('category')) {
            $query->whereHas('categories', function($q) use ($request) {
                $q->where('name', $request->category);
            });
        }

        $games = $query->orderByDesc('discount_percent')->paginate(20);
        
        // Manter parâmetros de filtro na paginação
        $games->appends($request->only(['search', 'store', 'min_discount', 'category']));
        
        // Obter lojas com nomes normalizados (apenas de deals válidos)
        $storeNames = Game::valid()
            ->distinct()
            ->whereNotNull('store_name')
            ->where('store_name', '!=', '')
            ->pluck('store_name')
            ->sort()
            ->values();
            
        $topGames = Game::valid()->orderByDesc('discount_percent')->take(5)->get();
        $categories = \App\Models\Category::orderBy('name')->get();

        // Retorna só o partial se for AJAX
        if ($request->ajax()) {
            return view('partials.games-list', compact('games'))->render();
        }

        return view('home', compact('games', 'storeNames', 'topGames', 'categories'));
    }
}
