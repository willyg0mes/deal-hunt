@extends('layouts.app')

@section('content')
<div x-data="{ showLogin: {{ request('register') ? 'false' : 'true' }}, show2FA: {{ session('login.id') ? 'true' : 'false' }} }"
     x-init="window.addEventListener('show-register', () => showLogin = false)"
     class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-gray-900 via-red-900 to-red-800 relative">

    <div class="w-full max-w-md" :class="show2FA ? 'opacity-50 pointer-events-none' : ''">
        {{-- Card de Login --}}
        <div
            x-show="showLogin"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 scale-75"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-400"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-75"
            class="bg-gray-900 border border-red-700 rounded-xl shadow-lg p-8 mb-4"
        >
            <h2 class="text-3xl font-bold text-red-400 mb-6 text-center">Entrar</h2>
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf
                <div>
                    <x-input-label for="email" :value="__('Email')" class="text-gray-200" />
                    <x-text-input id="email" class="block mt-1 w-full bg-gray-800 border border-red-700 text-gray-200" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-400" />
                </div>
                <div>
                    <x-input-label for="password" :value="__('Senha')" class="text-gray-200" />
                    <x-text-input id="password" class="block mt-1 w-full bg-gray-800 border border-red-700 text-gray-200"
                        type="password" name="password" required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-400" />
                </div>
                <div class="flex items-center justify-between">
                    <label for="remember_me" class="inline-flex items-center">
                        <input id="remember_me" type="checkbox" class="rounded bg-gray-900 border-red-700 text-red-600" name="remember">
                        <span class="ms-2 text-sm text-gray-200">Lembrar-me</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a class="underline text-sm text-red-400 hover:text-white transition" href="{{ route('password.request') }}">
                            Esqueceu a senha?
                        </a>
                    @endif
                </div>
                <x-primary-button class="w-full bg-red-700 hover:bg-red-800 text-white font-bold py-2 px-4 rounded transition duration-200">
                    Entrar
                </x-primary-button>
            </form>

            <div class="mt-6 text-center">
                <button @click="showLogin = false" class="text-red-400 hover:text-white underline transition">Registrar</button>
            </div>
        </div>

        {{-- Card de Registro --}}
        <div
            x-show="!showLogin"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 scale-75"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-400"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-75"
            class="bg-gray-900 border border-red-700 rounded-xl shadow-lg p-8 mb-4"
        >
            <h2 class="text-3xl font-bold text-red-400 mb-6 text-center">Registrar</h2>
            <form method="POST" action="{{ route('register') }}" class="space-y-6">
                @csrf
                <div>
                    <x-input-label for="name" :value="__('Nome')" class="text-gray-200" />
                    <x-text-input id="name" class="block mt-1 w-full bg-gray-800 border border-red-700 text-gray-200" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2 text-red-400" />
                </div>
                <div>
                    <x-input-label for="email" :value="__('Email')" class="text-gray-200" />
                    <x-text-input id="email" class="block mt-1 w-full bg-gray-800 border border-red-700 text-gray-200" type="email" name="email" :value="old('email')" required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-400" />
                </div>
                <div>
                    <x-input-label for="password" :value="__('Senha')" class="text-gray-200" />
                    <x-text-input id="password" class="block mt-1 w-full bg-gray-800 border border-red-700 text-gray-200"
                        type="password" name="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-400" />
                </div>
                <div>
                    <x-input-label for="password_confirmation" :value="__('Confirme a senha')" class="text-gray-200" />
                    <x-text-input id="password_confirmation" class="block mt-1 w-full bg-gray-800 border border-red-700 text-gray-200"
                        type="password" name="password_confirmation" required autocomplete="new-password" />
                </div>
                <x-primary-button class="w-full bg-red-700 hover:bg-red-800 text-white font-bold py-2 px-4 rounded transition duration-200">
                    Registrar
                </x-primary-button>
            </form>
            <div class="mt-6 text-center">
                <button @click="showLogin = true" class="text-red-400 hover:text-white underline transition">Já tenho conta</button>
            </div>
        </div>
    </div>

    {{-- Card/modal 2FA --}}
    <div
        x-show="show2FA"
        x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="opacity-0 scale-75"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-400"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-75"
        class="fixed inset-0 flex items-center justify-center z-50"
        style="background: rgba(0,0,0,0.6);"
    >
        <div class="bg-gray-900 border border-red-700 rounded-xl shadow-lg p-8 w-full max-w-md">
            <h2 class="text-2xl font-bold text-red-400 mb-6 text-center">Autenticação em duas etapas</h2>
            <form method="POST" action="{{ route('two-factor.login') }}">
                @csrf
                <input type="hidden" name="id" value="{{ session('login.id') }}">
                <div>
                    <x-input-label for="code" :value="'Código 2FA'" class="text-gray-200" />
                    <x-text-input id="code" class="block mt-1 w-full bg-gray-800 border border-red-700 text-gray-200"
                        type="text" name="code" required autofocus />
                    <x-input-error :messages="$errors->get('code')" class="mt-2 text-red-400" />
                </div>
                <x-primary-button class="w-full bg-red-700 hover:bg-red-800 text-white font-bold py-2 px-4 rounded transition duration-200">
                    Validar 2FA
                </x-primary-button>
                <label class="inline-flex items-center mt-2 text-gray-200">
                    <input type="checkbox" name="remember" value="1">
                    <span class="ml-2">Não pedir 2FA neste dispositivo</span>
                </label>
            </form>
        </div>
    </div>

    {{-- Alpine.js --}}
    <script src="//unpkg.com/alpinejs" defer></script>
</div>
@endsection
