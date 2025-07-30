<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\TrustedDevice;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class CheckTrustedDevice
{
    public function handle(Request $request, Closure $next)
    {
        // Só verifica na tela de challenge do 2FA
        if ($request->is('two-factor-challenge')) {
            $deviceId = $request->cookie('trusted_device');
            $loginId = $request->session()->get('login.id');

            if ($loginId && $deviceId) {
                $trusted = TrustedDevice::where('user_id', $loginId)
                    ->where('device_id', $deviceId)
                    ->where('expires_at', '>', now())
                    ->first();

                if ($trusted) {
                    // Autentica o usuário manualmente
                    $user = User::find($loginId);
                    if ($user) {
                        Auth::login($user);
                    }
                    $request->session()->forget('login.id');
                    // Redireciona para a home (ou intended)
                    return redirect()->intended('/');
                }
            }
        }

        return $next($request);
    }
}
