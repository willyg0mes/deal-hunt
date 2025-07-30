<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\LoginHistory;
use Illuminate\Support\Str;
use App\Models\TrustedDevice;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use App\Http\Middleware\CheckTrustedDevice;

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
        Log::info('DEBUG trusted_device', [
            'cookie_raw' => $_COOKIE['trusted_device'] ?? null,
            'cookie_via_request' => $request->cookie('trusted_device'),
            'trusted_in_db' => TrustedDevice::where('user_id', auth()->id())
                ->where('device_id', $request->cookie('trusted_device'))
                ->where('expires_at', '>', now())
                ->first(),
        ]);
        Log::info('Login: store method called', [
            'request_data' => $request->all(),
        ]);

        $request->authenticate();
        $request->session()->regenerate();

        $deviceId = $request->cookie('trusted_device');
        if ($deviceId) {
            $trusted = TrustedDevice::where('user_id', auth()->id())
                ->where('device_id', $deviceId)
                ->where('expires_at', '>', now())
                ->first();

            Log::info('Login: trusted_device encontrado?', [
                'trusted' => $trusted,
            ]);

            if ($trusted) {
                $request->session()->forget('login.id');
                return redirect()->intended('/');
            }
        }
        Log::info('Login: trusted_device não encontrado ou inválido', [
            'device_id' => $deviceId,
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Logout.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('home');
    }

    /**
     * Processa o código do 2FA.
     */
    public function twoFactor(Request $request)
    {
        Log::info('2FA: Método twoFactor chamado', [
            'user_id' => auth()->id(),
            'request_data' => $request->all(),
        ]);

        $userId = $request->input('id');
        $user = \App\Models\User::find($userId);

        // Validação TOTP para qualquer app autenticador
        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey(
            decrypt($user->two_factor_secret), // use decrypt se o campo estiver criptografado
            $request->input('code')
        );

        if (!$valid) {
            return back()->withErrors(['code' => 'Código 2FA inválido.']);
        }

        // Faça login do usuário manualmente
        Auth::login($user);

        // Limpe o login.id da sessão
        $request->session()->forget('login.id');

        // Salve o trusted device se marcado
        if ($request->filled('remember')) {
            $deviceId = $request->cookie('trusted_device');

            // Verifica se já existe trusted válido
            $trusted = TrustedDevice::where('user_id', $userId)
                ->where('device_id', $deviceId)
                ->where('expires_at', '>', now())
                ->first();

            if (!$trusted) {
                // Se não existe, cria um novo
                $deviceId = Str::uuid()->toString();
                $trusted = TrustedDevice::create([
                    'user_id'    => $userId,
                    'device_id'  => $deviceId,
                    'user_agent' => $request->header('User-Agent'),
                    'ip'         => $request->ip(),
                    'expires_at' => now()->addMonths(6),
                ]);
                Log::info('2FA: TrustedDevice criado', [
                    'trusted_device_id' => $trusted->id,
                    'device_id' => $deviceId,
                ]);
            } else {
                Log::info('2FA: TrustedDevice já existente', [
                    'trusted_device_id' => $trusted->id,
                    'device_id' => $deviceId,
                ]);
            }

            Log::info('Login: trusted_device', [
                'cookie' => $deviceId,
                'user_id' => auth()->id(),
            ]);

            return redirect()->intended('/')->withCookie(
                cookie('trusted_device', $deviceId, 60 * 24 * 180)
            );
        }

        Log::info('2FA: Não marcou remember, não salvou trusted device');

        return redirect()->intended('/');
    }
}
