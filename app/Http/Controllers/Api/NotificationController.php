<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Events\NewNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function getUnreadLatest()
    {
        $notifications = Auth::user()
            ->notifications()
            ->where('read', false)
            ->with('game')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'message' => $notification->message,
                    'created_at' => $notification->created_at->toISOString(),
                    'game' => $notification->game ? [
                        'id' => $notification->game->id,
                        'title' => $notification->game->title,
                    ] : null,
                ];
            }),
        ]);
    }

    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->update(['read' => true]);

        return response()->json(['success' => true]);
    }

    // Para testar as notificações (remove em produção)
    public function testNotification(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $notification = Notification::create([
            'user_id' => Auth::id(),
            'game_id' => null,
            'message' => $request->input('message', '🧨 Oferta teste chegou! Jogo com desconto especial disponível por tempo limitado!'),
            'read' => false,
        ]);

        // Disparar evento para WebSocket
        event(new NewNotification($notification, Auth::user()));

        return response()->json([
            'success' => true,
            'notification' => $notification,
        ]);
    }
}
