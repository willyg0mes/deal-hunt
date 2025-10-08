<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Notification;
use App\Events\NewNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

// Rotas de notificações com autenticação web
Route::middleware('auth:web')->get('/notifications/unread-latest', function (Request $request) {
    return response()->json([
        'notifications' => $request->user()
            ->notifications()
            ->where('read', false)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($n) => [
                'id' => $n->id, 
                'message' => $n->message,
                'created_at' => $n->created_at->toISOString()
            ])
    ]);
});

Route::middleware('auth:web')->post('/notifications/{id}/mark-as-read', function (Request $request, $id) {
    $notification = $request->user()->notifications()->find($id);
    if ($notification) {
        $notification->update(['read' => true]);
        return response()->json(['success' => true]);
    }
    return response()->json(['success' => false], 404);
});

// Rota para enviar notificação para TODOS os usuários (apenas para admins)
Route::middleware('auth:web')->post('/notifications/broadcast-all', function (Request $request) {
    $user = $request->user();
    
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Usuário não autenticado. Faça login novamente.',
        ], 401);
    }
    
    // Verificar se é admin
    if (!in_array($user->email, ['admin@dealhunt.com', 'admin@test.com'])) {
        return response()->json([
            'success' => false,
            'message' => 'Acesso negado. Apenas administradores podem enviar notificações para todos.',
        ], 403);
    }
    
    $message = $request->input('message', '🎮 Notificação global! Confira as últimas ofertas especiais!');
    $users = \App\Models\User::all();
    
    $notifications = [];
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($users as $targetUser) {
        try {
            $notification = Notification::create([
                'user_id' => $targetUser->id,
                'game_id' => null,
                'message' => $message,
                'read' => false,
            ]);
            
            // Disparar evento WebSocket
            try {
                event(new NewNotification($notification, $targetUser));
            } catch (Exception $e) {
                Log::warning("Erro WebSocket para usuário {$targetUser->id}: " . $e->getMessage());
            }
            
            $notifications[] = [
                'user_id' => $targetUser->id,
                'notification_id' => $notification->id,
                'user_name' => $targetUser->name,
            ];
            
            $successCount++;
            
        } catch (Exception $e) {
            $errorCount++;
            Log::error("Erro ao criar notificação para usuário {$targetUser->id}: " . $e->getMessage());
        }
    }
    
    return response()->json([
        'success' => true,
        'message' => $message,
        'stats' => [
            'total_users' => $users->count(),
            'success_count' => $successCount,
            'error_count' => $errorCount,
        ],
        'notifications' => $notifications,
    ]);
});

// Health Check para Docker/Coolify
Route::get('/health', function () {
    try {
        // Verifica conexão com o banco
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'database' => 'connected'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'timestamp' => now()->toISOString(),
            'database' => 'disconnected',
            'error' => $e->getMessage()
        ], 503);
    }
});