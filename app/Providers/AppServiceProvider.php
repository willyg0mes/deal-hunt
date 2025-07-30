<?php

namespace App\Providers;

use App\Models\Game;
use App\Observers\GameObserver;
use App\Observers\GameTranslationObserver;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Game::observe(GameObserver::class);
        Game::observe(GameTranslationObserver::class);
        
        if ($this->app->runningInConsole()) {
            return;
        }

        // ✅ Garante que a rota API personalizada seja carregada corretamente
        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/api.php'));
    }
}
