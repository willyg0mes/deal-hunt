<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Category;
use Illuminate\Http\Request;

class FreeGamesController extends Controller
{
    public function index(Request $request)
    {
        $query = Game::with('categories');
        
        // Filtros
        if ($request->has('filter')) {
            switch ($request->filter) {
                case 'free':
                    $query->free();
                    break;
                case 'not_free':
                    $query->where('is_free_permanently', false);
                    break;
                case 'price_zero':
                    $query->where(function($q) {
                        $q->where('normal_price', 0)->orWhere('sale_price', 0);
                    });
                    break;
                default:
                    // Todos os jogos
                    break;
            }
        }
        
        // Busca
        if ($request->has('search') && $request->search) {
            $query->where('title', 'LIKE', '%' . $request->search . '%');
        }
        
        $games = $query->orderBy('created_at', 'desc')->paginate(20);
        $categories = Category::orderBy('name')->get();
        
        // Estatísticas
        $stats = [
            'total' => Game::count(),
            'free_permanent' => Game::free()->count(),
            'price_zero' => Game::where(function($q) {
                $q->where('normal_price', 0)->orWhere('sale_price', 0);
            })->count(),
        ];
        
        return view('admin.free-games.index', compact('games', 'categories', 'stats'));
    }
    
    public function markAsFree(Request $request, Game $game)
    {
        $request->validate([
            'free_description' => 'nullable|string|max:500'
        ]);
        
        $game->update([
            'is_free_permanently' => true,
            'free_since' => now(),
            'free_description' => $request->free_description
        ]);
        
        return response()->json([
            'success' => true,
            'message' => "'{$game->title}' foi marcado como gratuito permanente!"
        ]);
    }
    
    public function removeFree(Game $game)
    {
        $game->update([
            'is_free_permanently' => false,
            'free_since' => null,
            'free_description' => null
        ]);
        
        return response()->json([
            'success' => true,
            'message' => "'{$game->title}' foi removido dos jogos gratuitos."
        ]);
    }
    
    public function bulkMarkAsFree(Request $request)
    {
        $request->validate([
            'game_ids' => 'required|array',
            'game_ids.*' => 'exists:games,id',
            'free_description' => 'nullable|string|max:500'
        ]);
        
        $count = Game::whereIn('id', $request->game_ids)->update([
            'is_free_permanently' => true,
            'free_since' => now(),
            'free_description' => $request->free_description
        ]);
        
        return response()->json([
            'success' => true,
            'message' => "{$count} jogos foram marcados como gratuitos permanentes!"
        ]);
    }
    
    public function autoDetectFreeGames()
    {
        // Detectar jogos que têm preço 0 mas não estão marcados como gratuitos permanentes
        $candidates = Game::where(function($q) {
            $q->where('normal_price', 0)->orWhere('sale_price', 0);
        })
        ->where('is_free_permanently', false)
        ->with('categories')
        ->get();
        
        return response()->json([
            'success' => true,
            'candidates' => $candidates->map(function($game) {
                return [
                    'id' => $game->id,
                    'title' => $game->title,
                    'store_name' => $game->store_name,
                    'normal_price' => $game->normal_price,
                    'sale_price' => $game->sale_price,
                    'categories' => $game->categories->pluck('name')->implode(', ')
                ];
            })
        ]);
    }
}
