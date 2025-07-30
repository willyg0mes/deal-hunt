<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Contracts\TwoFactorChallengeViewResponse;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse;
use App\Http\Responses\CustomTwoFactorLoginResponse;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;


class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            TwoFactorLoginResponse::class,
            CustomTwoFactorLoginResponse::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->email.$request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id').$request->ip());
        });

        $this->app->singleton(
            \Laravel\Fortify\Contracts\TwoFactorChallengeViewResponse::class,
            function () {
                return new class implements \Laravel\Fortify\Contracts\TwoFactorChallengeViewResponse {
                    public function toResponse($request)
                    {
                        return view('auth.login');
                    }
                };
            }
        );

        Fortify::twoFactorChallengeView(function () {
            return view('auth.login');
        });

        Fortify::requestPasswordResetLinkView(function () {
            return view('auth.forgot-password');
        });

        // Escuta o evento de login para salvar o histórico
        Event::listen(Login::class, function ($event) {
            \App\Models\LoginHistory::create([
                'user_id' => $event->user->id,
                'ip' => request()->ip(),
                'device' => request()->header('User-Agent'),
                'logged_in_at' => now(),
            ]);
        });
    }
}
