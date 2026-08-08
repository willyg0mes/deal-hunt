@php
    $bag = $errors ?? new \Illuminate\Support\ViewErrorBag();
    $startOnRegister = request()->boolean('register')
        || ($showRegister ?? false)
        || $bag->has('name')
        || $bag->has('password_confirmation');
    $pageTitle = ($startOnRegister ? 'Criar conta' : 'Entrar').' | '.config('app.name', 'DealHunt');
@endphp

<x-guest-layout :title="$pageTitle">
<div
    x-data="{ showLogin: {{ $startOnRegister ? 'false' : 'true' }}, show2FA: {{ session('login.id') ? 'true' : 'false' }} }"
    x-cloak
    class="relative"
>
    <div class="w-full" :class="show2FA ? 'opacity-40 pointer-events-none' : ''">
        <div
            x-show="showLogin"
            x-transition.opacity.duration.300ms
            class="rounded-2xl border border-red-800/50 bg-gray-950/80 backdrop-blur-sm shadow-2xl shadow-black/40 p-6 sm:p-8"
        >
            <h1 class="text-2xl font-bold text-red-400 mb-6 text-center">Entrar</h1>
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                <div>
                    <x-input-label for="email" value="Email" class="text-gray-300" />
                    <x-text-input id="email" class="block mt-1 w-full bg-gray-900 border-red-800/70 text-gray-100 focus:border-red-500 focus:ring-red-500" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-400" />
                </div>
                <div>
                    <x-input-label for="password" value="Senha" class="text-gray-300" />
                    <x-text-input id="password" class="block mt-1 w-full bg-gray-900 border-red-800/70 text-gray-100 focus:border-red-500 focus:ring-red-500" type="password" name="password" required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-400" />
                </div>
                <div class="flex items-center justify-between gap-3">
                    <label for="remember_me" class="inline-flex items-center">
                        <input id="remember_me" type="checkbox" class="rounded bg-gray-900 border-red-800 text-red-600 focus:ring-red-500" name="remember">
                        <span class="ms-2 text-sm text-gray-300">Lembrar-me</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a class="text-sm text-red-400 hover:text-red-300 underline underline-offset-2" href="{{ route('password.request') }}">
                            Esqueceu a senha?
                        </a>
                    @endif
                </div>
                <button type="submit" class="w-full bg-red-700 hover:bg-red-600 text-white font-bold py-2.5 px-4 rounded-lg transition">
                    Entrar
                </button>
            </form>

            <div class="mt-6 text-center text-sm text-gray-400">
                Não tem conta?
                <button type="button" @click="showLogin = false" class="text-red-400 hover:text-red-300 underline underline-offset-2 ml-1">Registrar</button>
            </div>
        </div>

        <div
            x-show="!showLogin"
            x-transition.opacity.duration.300ms
            class="rounded-2xl border border-red-800/50 bg-gray-950/80 backdrop-blur-sm shadow-2xl shadow-black/40 p-6 sm:p-8"
        >
            <h1 class="text-2xl font-bold text-red-400 mb-6 text-center">Criar conta</h1>
            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf
                <div>
                    <x-input-label for="name" value="Nome" class="text-gray-300" />
                    <x-text-input id="name" class="block mt-1 w-full bg-gray-900 border-red-800/70 text-gray-100 focus:border-red-500 focus:ring-red-500" type="text" name="name" :value="old('name')" required autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2 text-red-400" />
                </div>
                <div>
                    <x-input-label for="register_email" value="Email" class="text-gray-300" />
                    <x-text-input id="register_email" class="block mt-1 w-full bg-gray-900 border-red-800/70 text-gray-100 focus:border-red-500 focus:ring-red-500" type="email" name="email" :value="old('email')" required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-400" />
                </div>
                <div>
                    <x-input-label for="register_password" value="Senha" class="text-gray-300" />
                    <x-text-input id="register_password" class="block mt-1 w-full bg-gray-900 border-red-800/70 text-gray-100 focus:border-red-500 focus:ring-red-500" type="password" name="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-400" />
                </div>
                <div>
                    <x-input-label for="password_confirmation" value="Confirme a senha" class="text-gray-300" />
                    <x-text-input id="password_confirmation" class="block mt-1 w-full bg-gray-900 border-red-800/70 text-gray-100 focus:border-red-500 focus:ring-red-500" type="password" name="password_confirmation" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-red-400" />
                </div>
                <button type="submit" class="w-full bg-red-700 hover:bg-red-600 text-white font-bold py-2.5 px-4 rounded-lg transition">
                    Registrar
                </button>
            </form>
            <div class="mt-6 text-center text-sm text-gray-400">
                Já tem conta?
                <button type="button" @click="showLogin = true" class="text-red-400 hover:text-red-300 underline underline-offset-2 ml-1">Entrar</button>
            </div>
        </div>
    </div>

    <div
        x-show="show2FA"
        x-transition.opacity.duration.300ms
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4"
    >
        <div class="w-full max-w-md rounded-2xl border border-red-800/50 bg-gray-950 p-6 sm:p-8 shadow-2xl">
            <h2 class="text-xl font-bold text-red-400 mb-5 text-center">Autenticação em duas etapas</h2>
            <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="id" value="{{ session('login.id') }}">
                <div>
                    <x-input-label for="code" value="Código 2FA" class="text-gray-300" />
                    <x-text-input id="code" class="block mt-1 w-full bg-gray-900 border-red-800/70 text-gray-100 focus:border-red-500 focus:ring-red-500" type="text" name="code" required autofocus />
                    <x-input-error :messages="$errors->get('code')" class="mt-2 text-red-400" />
                </div>
                <label class="inline-flex items-center text-sm text-gray-300">
                    <input type="checkbox" name="remember" value="1" class="rounded bg-gray-900 border-red-800 text-red-600 focus:ring-red-500">
                    <span class="ml-2">Não pedir 2FA neste dispositivo</span>
                </label>
                <button type="submit" class="w-full bg-red-700 hover:bg-red-600 text-white font-bold py-2.5 px-4 rounded-lg transition">
                    Validar 2FA
                </button>
            </form>
        </div>
    </div>
</div>
</x-guest-layout>
