<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\TrustedDevice;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class AuthenticatedSessionController extends Controller
{
    /**
     * Exibe a tela de login.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Processa o login.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $deviceId = $request->cookie('trusted_device');
        if ($deviceId) {
            $trusted = TrustedDevice::query()
                ->where('user_id', auth()->id())
                ->where('device_id', $deviceId)
                ->where('expires_at', '>', now())
                ->first();

            if ($trusted) {
                $request->session()->forget('login.id');

                return redirect()->intended('/');
            }
        }

        return redirect()->intended('/');
    }

    /**
     * Logout.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    /**
     * Processa o código do 2FA (TOTP ou recovery).
     */
    public function twoFactor(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        // Bind challenge to Fortify session login.id — never trust body id.
        $userId = $request->session()->get('login.id');
        if ($userId === null || $userId === '') {
            return back()->withErrors(['code' => 'Sessão 2FA inválida. Faça login novamente.']);
        }

        $user = User::query()->find($userId);

        if ($user === null || blank($user->two_factor_secret)) {
            return back()->withErrors(['code' => 'Sessão 2FA inválida. Faça login novamente.']);
        }

        $code = preg_replace('/\s+/', '', (string) $request->input('code'));
        $valid = false;

        try {
            $google2fa = new Google2FA;
            $valid = $google2fa->verifyKey(
                decrypt($user->two_factor_secret),
                $code
            );
        } catch (\Throwable $e) {
            Log::warning('2FA verify failed', ['user_id' => $userId, 'error' => $e->getMessage()]);
        }

        if (! $valid && method_exists($user, 'recoveryCodes')) {
            $recoveryCodes = $user->recoveryCodes();
            foreach ($recoveryCodes as $recoveryCode) {
                if (hash_equals((string) $recoveryCode, $code)) {
                    $user->replaceRecoveryCode($recoveryCode);
                    $valid = true;
                    break;
                }
            }
        }

        if (! $valid) {
            return back()->withErrors(['code' => 'Código 2FA inválido.']);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->forget('login.id');
        $request->session()->regenerate();

        if ($request->filled('remember') || $request->boolean('remember_device')) {
            $deviceId = $request->cookie('trusted_device');

            $trusted = $deviceId
                ? TrustedDevice::query()
                    ->where('user_id', $userId)
                    ->where('device_id', $deviceId)
                    ->where('expires_at', '>', now())
                    ->first()
                : null;

            if (! $trusted) {
                $deviceId = Str::uuid()->toString();
                TrustedDevice::query()->create([
                    'user_id' => $userId,
                    'device_id' => $deviceId,
                    'user_agent' => $request->header('User-Agent'),
                    'ip' => $request->ip(),
                    'expires_at' => now()->addMonths(6),
                ]);
            }

            return redirect()->intended('/')->withCookie(
                cookie('trusted_device', $deviceId, 60 * 24 * 180)
            );
        }

        return redirect()->intended('/');
    }
}
