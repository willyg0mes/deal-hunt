@extends('layouts.app')

@section('content')
<div class="py-8 min-h-screen bg-gradient-to-br from-gray-900 via-red-900 to-red-800 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
    <div class="max-w-3xl mx-auto px-4 space-y-6">

        {{-- Informações do Perfil --}}
        <div class="p-4 bg-gray-900 dark:bg-gray-800 rounded-md shadow-sm border border-red-700 dark:border-gray-600 flex items-center gap-6">
            <div>
                <img src="{{ Auth::user()->profile_photo_url ?? asset('images/default-avatar.png') }}"
                     alt="Foto de perfil"
                     class="w-20 h-20 rounded-full object-cover border-2 border-red-700 dark:border-gray-500 shadow" />
            </div>
            <form method="POST" action="{{ route('profile.photo') }}" enctype="multipart/form-data" class="flex flex-col gap-2">
                @csrf
                <input type="file" name="profile_photo" accept="image/*" class="block text-gray-200 dark:text-gray-300 file:bg-red-700 dark:file:bg-gray-600 file:text-white dark:file:text-gray-200 file:border-0 file:rounded file:px-3 file:py-1" />
                <button type="submit" class="bg-red-700 hover:bg-red-800 dark:bg-gray-600 dark:hover:bg-gray-700 text-white px-4 py-1 rounded transition-colors">Atualizar foto</button>
            </form>
        </div>

        {{-- Dados e Segurança --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="p-4 bg-gray-900 dark:bg-gray-800 rounded-md shadow-sm border border-red-700 dark:border-gray-600">
                @include('profile.partials.update-profile-information-form')
            </div>
            <div class="p-4 bg-gray-900 dark:bg-gray-800 rounded-md shadow-sm border border-red-700 dark:border-gray-600">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="p-4 bg-gray-900 dark:bg-gray-800 rounded-md shadow-sm border border-red-700 dark:border-gray-600">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>

        {{-- Segurança --}}
        <div class="p-4 bg-gray-900 dark:bg-gray-800 rounded-md shadow-sm border border-red-700 dark:border-gray-600">
            <h2 class="font-bold mb-2 text-red-400 dark:text-gray-300">Segurança</h2>
            <ul class="text-sm text-gray-300 dark:text-gray-400 space-y-1">
                <li>E-mail verificado: <span class="font-bold">{{ Auth::user()->hasVerifiedEmail() ? 'Sim' : 'Não' }}</span></li>
                <li>Último login: <span class="font-bold">{{ Auth::user()->last_login_at ? Auth::user()->last_login_at->diffForHumans() : 'N/A' }}</span></li>
                <li>
                    2FA: 
                    @if(Auth::user()->two_factor_secret)
                        <span class="font-bold text-green-400">Ativado</span>
                        <form method="POST" action="{{ route('two-factor.disable') }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="ml-2 text-red-400 dark:text-red-300 underline hover:text-red-300 dark:hover:text-red-200 transition-colors">Desativar</button>
                        </form>
                    @else
                        <span class="font-bold text-red-400 dark:text-red-300">Desativado</span>
                        <form method="POST" action="{{ route('two-factor.enable') }}" class="inline">
                            @csrf
                            <button type="submit" class="ml-2 text-green-400 dark:text-green-300 underline hover:text-green-300 dark:hover:text-green-200 transition-colors">Ativar</button>
                        </form>
                    @endif
                </li>
            </ul>
            @if(Auth::user()->two_factor_secret)
                <div class="mt-4">
                    <h3 class="font-bold text-red-400 dark:text-gray-300 mb-2">Escaneie o QR Code no seu app autenticador:</h3>
                    <div class="bg-gray-800 dark:bg-gray-700 p-4 rounded flex justify-center">
                        {!! auth()->user()->twoFactorQrCodeSvg() !!}
                    </div>
                    <h4 class="font-bold text-red-400 dark:text-gray-300 mt-4 mb-2">Códigos de recuperação:</h4>
                    <ul class="bg-gray-800 dark:bg-gray-700 p-4 rounded grid grid-cols-2 gap-2 text-xs">
                        @foreach(auth()->user()->recoveryCodes() as $code)
                            <li class="bg-gray-900 dark:bg-gray-600 rounded px-2 py-1 font-mono text-gray-200 dark:text-gray-300">{{ $code }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        {{-- Histórico de Logins e Dispositivos Confiáveis --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="p-4 bg-gray-900 dark:bg-gray-800 rounded-md shadow-sm border border-red-700 dark:border-gray-600">
                <h3 class="font-bold mb-2 text-red-400 dark:text-gray-300">Histórico de logins</h3>
                <ul class="bg-gray-800 dark:bg-gray-700 p-4 rounded text-xs text-gray-200 dark:text-gray-300 space-y-1">
                    @foreach(Auth::user()->loginHistories()->latest('logged_in_at')->limit(10)->get() as $login)
                        <li class="flex items-center gap-2 text-xs">
                            <span class="text-gray-300 dark:text-gray-400">
                                {{ \Carbon\Carbon::parse($login->logged_in_at)->format('d/m/Y H:i') }}
                            </span>
                            <span class="text-gray-400 dark:text-gray-500">•</span>
                            <span class="text-gray-200 dark:text-gray-300">IP: {{ $login->ip }}</span>
                            <span class="text-gray-400 dark:text-gray-500">•</span>
                            <span class="text-gray-200 dark:text-gray-300">
                                Navegador: 
                                {{ 
                                    Str::contains($login->device, 'Chrome') ? 'Chrome' : 
                                    (Str::contains($login->device, 'Firefox') ? 'Firefox' : 
                                    (Str::contains($login->device, 'Safari') ? 'Safari' : 
                                    (Str::contains($login->device, 'Edge') ? 'Edge' : 'Outro')))
                                }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="p-4 bg-gray-900 dark:bg-gray-800 rounded-md shadow-sm border border-red-700 dark:border-gray-600">
                <h3 class="font-bold mb-2 text-red-400 dark:text-gray-300">Dispositivos confiáveis</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach(Auth::user()->trustedDevices as $device)
                        @php
                            $ua = $device->user_agent;
                            $browser = Str::contains($ua, 'Edge') ? 'Edge' :
                                       (Str::contains($ua, 'Chrome') ? 'Chrome' :
                                       (Str::contains($ua, 'Firefox') ? 'Firefox' :
                                       (Str::contains($ua, 'Safari') ? 'Safari' : 'Outro')));
                            $os = Str::contains($ua, 'Windows') ? 'Windows' :
                                  (Str::contains($ua, 'Macintosh') ? 'Mac' :
                                  (Str::contains($ua, 'Linux') ? 'Linux' :
                                  (Str::contains($ua, 'Android') ? 'Android' :
                                  (Str::contains($ua, 'iPhone') ? 'iPhone' :
                                  (Str::contains($ua, 'iPad') ? 'iPad' : 'Outro')))));
                            $deviceType = (Str::contains($ua, 'Mobile') || Str::contains($ua, 'Android') || Str::contains($ua, 'iPhone') || Str::contains($ua, 'iPad')) ? 'Mobile' : 'Desktop';
                            $icon = $deviceType === 'Mobile' ? '📱' : '💻';
                        @endphp
                        <div class="bg-gray-800 dark:bg-gray-700 rounded-lg shadow p-4 flex flex-col items-center border border-red-700 dark:border-gray-600">
                            <div class="text-4xl mb-2">{{ $icon }}</div>
                            <div class="font-bold text-red-400 dark:text-gray-300 mb-1">{{ $os }} ({{ $deviceType }})</div>
                            <div class="text-gray-300 dark:text-gray-400 text-sm mb-1">Navegador: {{ $browser }}</div>
                            <div class="text-gray-200 dark:text-gray-300 text-xs mb-1">IP: {{ $device->ip }}</div>
                            <div class="text-gray-400 dark:text-gray-500 text-xs">Expira: {{ \Carbon\Carbon::parse($device->expires_at)->format('d/m/Y H:i') }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
