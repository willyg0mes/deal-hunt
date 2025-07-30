<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index()
    {
        $games = auth()->user()->wishlist()->get();
        return view('wishlist.index', compact('games'));
    }

    public function toggle(Game $game, Request $request)
    {
        $user = auth()->user();

        if ($user->wishlist()->where('game_id', $game->id)->exists()) {
            $user->wishlist()->detach($game->id);
            $message = "'{$game->title}' foi removido da sua wishlist!";
            $type = 'info';
            $isInWishlist = false;
        } else {
            $user->wishlist()->attach($game->id);
            $message = "'{$game->title}' foi adicionado à sua wishlist!";
            $type = 'success';
            $isInWishlist = true;
        }

        // Se for requisição AJAX, retornar JSON
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'type' => $type,
                'isInWishlist' => $isInWishlist
            ]);
        }

        // Preserve todos os parâmetros da URL de origem para requisições normais
        $redirectUrl = $request->header('referer', route('games.index'));
        
        return redirect()->to($redirectUrl)->with([
            'status' => $message,
            'alert_type' => $type
        ]);
    }
}
