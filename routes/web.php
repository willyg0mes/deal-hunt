<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\Admin\TranslationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Middleware\CheckTrustedDevice;

Route::middleware(['web'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    
    // Rotas públicas - não requerem autenticação
    Route::get('/games/{game}', [GameController::class, 'show'])->name('games.show');
    Route::get('/games', [GameController::class, 'index'])->name('games.index');
    Route::get('/games/free', [GameController::class, 'free'])->name('games.free');

    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // Rotas que requerem autenticação
        Route::get('/games/{game}/offers', [GameController::class, 'offers'])->name('games.offers');
        Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
        Route::post('/wishlist/toggle/{game}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

        Route::get('/notificacoes', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notificacoes/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::delete('/notificacoes/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
        Route::post('/notificacoes/marcar-todas', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAll');

        // 🌐 Rotas do Painel Administrativo de Traduções
        Route::prefix('admin/translations')->name('admin.translations.')->group(function () {
            Route::get('/', [TranslationController::class, 'index'])->name('index');
            Route::post('/batch', [TranslationController::class, 'translateBatch'])->name('batch');
            Route::post('/single/{game}', [TranslationController::class, 'translateSingle'])->name('single');
            Route::post('/game/{game}', [TranslationController::class, 'translateSingle'])->name('game');
            Route::post('/translate-all', [TranslationController::class, 'translateAll'])->name('translate-all');
            Route::delete('/{translation}', [TranslationController::class, 'deleteTranslation'])->name('delete');
            Route::get('/detect-new', [TranslationController::class, 'detectNewGames'])->name('detect-new');
            Route::post('/artisan', [TranslationController::class, 'runArtisanCommand'])->name('artisan');
            // Rotas para sistema assíncrono  
            Route::post('/batch/progress', [TranslationController::class, 'checkBatchProgress'])->name('batch.progress');
            Route::get('/batch/active', [TranslationController::class, 'listActiveBatches'])->name('batch.active');
            
            // Rotas para atualização de descrições
            Route::post('/descriptions/update', [TranslationController::class, 'updateAllDescriptions'])->name('descriptions.update');
            Route::post('/descriptions/progress', [TranslationController::class, 'checkDescriptionUpdateProgress'])->name('descriptions.progress');
            Route::get('/descriptions/stats', [TranslationController::class, 'getDescriptionStats'])->name('descriptions.stats');
        });

        // Adicione esta rota:
        Route::post('/logout', function (Illuminate\Http\Request $request) {
            \Illuminate\Support\Facades\Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/');
        })->name('logout');

        // Rota para atualizar a senha do usuário
        Route::patch('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
        Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo');

        Route::post('/profile/2fa-enable', [TwoFactorAuthenticationController::class, 'store'])->name('two-factor.enable');
        Route::delete('/profile/2fa-disable', [TwoFactorAuthenticationController::class, 'destroy'])->name('two-factor.disable');
    });

    // Rota para enviar o e-mail de verificação
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    })->middleware(['auth', 'throttle:6,1'])->name('verification.send');

    // Rota de login
    Route::get('/login', function () {
        // Você pode retornar uma view personalizada ou usar a padrão
        return view('auth.login');
    })->name('login');

    // Rota de registro
    Route::get('/register', function () {
        // Você pode retornar uma view personalizada ou usar a padrão
        return view('auth.register');
    })->name('register');
});

// Rotas administrativas
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [App\Http\Controllers\Admin\DashboardController::class, 'users'])->name('users');
    Route::post('/users/{user}/toggle-admin', [App\Http\Controllers\Admin\DashboardController::class, 'toggleAdmin'])->name('users.toggle-admin');
    Route::get('/logs', [App\Http\Controllers\Admin\DashboardController::class, 'logs'])->name('logs');
    Route::get('/controls', [App\Http\Controllers\Admin\DashboardController::class, 'controls'])->name('controls');
    Route::post('/fetch-deals', [App\Http\Controllers\Admin\DashboardController::class, 'fetchDeals'])->name('fetch-deals');
    Route::get('/deals/progress', [App\Http\Controllers\Admin\DashboardController::class, 'dealsProgress'])->name('deals.progress');
    
    // Rotas para limpeza de deals expirados
    Route::post('/cleanup/verify', [App\Http\Controllers\Admin\DashboardController::class, 'verifyExpiredDeals'])->name('cleanup.verify');
    Route::post('/cleanup/remove', [App\Http\Controllers\Admin\DashboardController::class, 'removeExpiredDeals'])->name('cleanup.remove');
    
    // Rotas para controles administrativos
    Route::post('/update-images', [App\Http\Controllers\Admin\DashboardController::class, 'updateImages'])->name('update-images');
    Route::post('/verify-expired-deals', [App\Http\Controllers\Admin\DashboardController::class, 'verifyExpiredDeals'])->name('verify-expired-deals');
    Route::delete('/remove-expired-deals', [App\Http\Controllers\Admin\DashboardController::class, 'removeExpiredDeals'])->name('remove-expired-deals');
    Route::get('/system-stats', [App\Http\Controllers\Admin\DashboardController::class, 'getSystemStats'])->name('system-stats');
    
    // Rotas para monitoramento de traduções
    Route::get('/translations/progress', [App\Http\Controllers\Admin\TranslationController::class, 'getTranslationProgress'])->name('translations.progress');
    Route::get('/translations/critical-status', [App\Http\Controllers\Admin\TranslationController::class, 'getCriticalTranslationsStatus'])->name('translations.critical-status');
    
    // 💰 Rotas para gerenciamento de moedas
    Route::prefix('currency')->name('currency.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\CurrencyController::class, 'index'])->name('index');
        Route::post('/update-rates', [App\Http\Controllers\Admin\CurrencyController::class, 'updateRates'])->name('update-rates');
        Route::get('/current-rates', [App\Http\Controllers\Admin\CurrencyController::class, 'getCurrentRates'])->name('current-rates');
        Route::post('/refresh-cache', [App\Http\Controllers\Admin\CurrencyController::class, 'refreshCache'])->name('refresh-cache');
        Route::get('/stats', [App\Http\Controllers\Admin\CurrencyController::class, 'getStats'])->name('stats');
    });
    
    // 🎮 Rotas para gerenciamento de jogos gratuitos
    Route::prefix('free-games')->name('free-games.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\FreeGamesController::class, 'index'])->name('index');
        Route::post('/{game}/mark-free', [App\Http\Controllers\Admin\FreeGamesController::class, 'markAsFree'])->name('mark-free');
        Route::delete('/{game}/remove-free', [App\Http\Controllers\Admin\FreeGamesController::class, 'removeFree'])->name('remove-free');
        Route::post('/bulk-mark-free', [App\Http\Controllers\Admin\FreeGamesController::class, 'bulkMarkAsFree'])->name('bulk-mark-free');
        Route::post('/auto-detect', [App\Http\Controllers\Admin\FreeGamesController::class, 'autoDetectFreeGames'])->name('auto-detect');
    });
});

// GET: challenge (com middleware para pular 2FA se trusted)
Route::get(
    '/two-factor-challenge',
    [\Laravel\Fortify\Http\Controllers\TwoFactorAuthenticatedSessionController::class, 'create']
)->middleware(CheckTrustedDevice::class)->name('two-factor.login');

// POST: challenge (usa seu controller customizado)
Route::post(
    '/two-factor-challenge',
    [AuthenticatedSessionController::class, 'twoFactor']
)->name('two-factor.login.store');

// Rotas para notificações (Web routes para melhor compatibilidade com sessões)
Route::middleware('auth')->group(function () {
    // Verificar novas notificações (para polling)
    Route::get('/notifications/check-new-web', function (Request $request) {
        $user = $request->user();
        
        // Pegar timestamp da última verificação
        $lastCheck = $request->get('last_check');
        if ($lastCheck) {
            $lastCheckTime = \Carbon\Carbon::createFromTimestamp($lastCheck / 1000);
        } else {
            $lastCheckTime = \Carbon\Carbon::now()->subMinutes(5);
        }
        
        $newNotifications = $user->notifications()
            ->where('read', false)
            ->where('created_at', '>', $lastCheckTime)
            ->latest()
            ->get()
            ->map(function($notification) {
                return [
                    'id' => $notification->id,
                    'message' => $notification->message,
                    'created_at' => $notification->created_at->toISOString(),
                    'game' => $notification->game ? [
                        'title' => $notification->game->title,
                        'id' => $notification->game->id,
                    ] : null,
                ];
            });
        
        return response()->json([
            'success' => true,
            'hasNew' => $newNotifications->count() > 0,
            'notifications' => $newNotifications,
            'count' => $newNotifications->count(),
            'timestamp' => now()->timestamp * 1000, // JavaScript timestamp
        ]);
    })->middleware('auth');
    
    // Buscar notificações não lidas (versão web)
    Route::get('/notifications/unread-latest-web', function (Request $request) {
        $user = $request->user();
        
        $notifications = $user->notifications()
            ->where('read', false)
            ->latest()
            ->take(10)
            ->get()
            ->map(function($notification) {
                return [
                    'id' => $notification->id,
                    'message' => $notification->message,
                    'created_at' => $notification->created_at->toISOString(),
                    'game' => $notification->game ? [
                        'title' => $notification->game->title,
                        'id' => $notification->game->id,
                    ] : null,
                ];
            });
        
        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'count' => $notifications->count(),
        ]);
    });
});