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
    x-data="{
        showLogin: {{ $startOnRegister ? 'false' : 'true' }},
        show2FA: {{ session('login.id') ? 'true' : 'false' }},
        showPass: false,
        showRegPass: false,
        showRegPass2: false
    }"
    x-cloak
    class="relative"
>
    <div class="w-full" :class="show2FA ? 'opacity-40 pointer-events-none' : ''">
        <div class="auth-card rounded-2xl p-6 sm:p-8">
            <div class="mb-6 grid grid-cols-2 gap-1 rounded-xl bg-slate-950/70 p-1 ring-1 ring-red-900/40">
                <button
                    type="button"
                    @click="showLogin = true"
                    class="auth-tab rounded-lg py-2.5 text-sm font-semibold text-slate-400"
                    :class="showLogin && 'auth-tab-active'"
                >
                    Entrar
                </button>
                <button
                    type="button"
                    @click="showLogin = false"
                    class="auth-tab rounded-lg py-2.5 text-sm font-semibold text-slate-400"
                    :class="!showLogin && 'auth-tab-active'"
                >
                    Criar conta
                </button>
            </div>

            <div x-show="showLogin" x-transition.opacity.duration.200ms>
                <p class="text-center text-sm text-slate-400 mb-5">Acesse wishlist, alertas e suas ofertas salvas.</p>
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="email" value="Email" class="text-slate-300" />
                        <x-text-input id="email" class="auth-input block mt-1.5 w-full rounded-xl" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="voce@email.com" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <x-input-label for="password" value="Senha" class="text-slate-300" />
                        <div class="relative mt-1.5">
                            <x-text-input
                                id="password"
                                class="auth-input block w-full rounded-xl pe-20"
                                x-bind:type="showPass ? 'text' : 'password'"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                            />
                            <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 px-3 text-xs font-bold text-red-400 hover:text-red-300">
                                <span x-text="showPass ? 'Ocultar' : 'Exibir'"></span>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-400" />
                    </div>
                    <div class="flex items-center justify-between gap-3 pt-1">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me" type="checkbox" class="rounded bg-slate-900 border-red-800 text-red-600 focus:ring-red-500" name="remember">
                            <span class="ms-2 text-sm text-slate-300">Lembrar-me</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a class="text-sm text-red-400 hover:text-red-300 underline underline-offset-2" href="{{ route('password.request') }}">
                                Esqueceu a senha?
                            </a>
                        @endif
                    </div>
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-500 text-white font-bold py-3 px-4 rounded-xl transition shadow-lg shadow-red-950/40">
                        Entrar
                    </button>
                </form>
            </div>

            <div x-show="!showLogin" x-transition.opacity.duration.200ms>
                <p class="text-center text-sm text-slate-400 mb-5">Crie sua conta e acompanhe descontos em tempo real.</p>
                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="name" value="Nome" class="text-slate-300" />
                        <x-text-input id="name" class="auth-input block mt-1.5 w-full rounded-xl" type="text" name="name" :value="old('name')" required autocomplete="name" placeholder="Seu nome" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <x-input-label for="register_email" value="Email" class="text-slate-300" />
                        <x-text-input id="register_email" class="auth-input block mt-1.5 w-full rounded-xl" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="voce@email.com" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <x-input-label for="register_password" value="Senha" class="text-slate-300" />
                        <div class="relative mt-1.5">
                            <x-text-input
                                id="register_password"
                                class="auth-input block w-full rounded-xl pe-20"
                                x-bind:type="showRegPass ? 'text' : 'password'"
                                name="password"
                                required
                                autocomplete="new-password"
                                placeholder="••••••••"
                            />
                            <button type="button" @click="showRegPass = !showRegPass" class="absolute inset-y-0 right-0 px-3 text-xs font-bold text-red-400 hover:text-red-300">
                                <span x-text="showRegPass ? 'Ocultar' : 'Exibir'"></span>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-400" />
                    </div>
                    <div>
                        <x-input-label for="password_confirmation" value="Confirme a senha" class="text-slate-300" />
                        <div class="relative mt-1.5">
                            <x-text-input
                                id="password_confirmation"
                                class="auth-input block w-full rounded-xl pe-20"
                                x-bind:type="showRegPass2 ? 'text' : 'password'"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                                placeholder="••••••••"
                            />
                            <button type="button" @click="showRegPass2 = !showRegPass2" class="absolute inset-y-0 right-0 px-3 text-xs font-bold text-red-400 hover:text-red-300">
                                <span x-text="showRegPass2 ? 'Ocultar' : 'Exibir'"></span>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-red-400" />
                    </div>
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-500 text-white font-bold py-3 px-4 rounded-xl transition shadow-lg shadow-red-950/40">
                        Criar conta
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div
        x-show="show2FA"
        x-transition.opacity.duration.300ms
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 px-4"
    >
        <div class="w-full max-w-md auth-card rounded-2xl p-6 sm:p-8">
            <div class="flex justify-center mb-4">
                <img src="{{ asset('images/brand/icon.png') }}" alt="" class="h-12 w-12 rounded-xl ring-1 ring-red-500/40 object-cover">
            </div>
            <h2 class="text-xl font-bold text-red-400 mb-2 text-center">Autenticação em duas etapas</h2>
            <p class="text-sm text-slate-400 text-center mb-5">Digite o código do seu aplicativo autenticador.</p>
            <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="id" value="{{ session('login.id') }}">
                <div>
                    <x-input-label for="code" value="Código 2FA" class="text-slate-300" />
                    <x-text-input id="code" class="auth-input block mt-1.5 w-full rounded-xl tracking-[0.3em] text-center text-lg" type="text" name="code" required autofocus inputmode="numeric" autocomplete="one-time-code" />
                    <x-input-error :messages="$errors->get('code')" class="mt-2 text-red-400" />
                </div>
                <label class="inline-flex items-center text-sm text-slate-300">
                    <input type="checkbox" name="remember" value="1" class="rounded bg-slate-900 border-red-800 text-red-600 focus:ring-red-500">
                    <span class="ml-2">Não pedir 2FA neste dispositivo</span>
                </label>
                <button type="submit" class="w-full bg-red-600 hover:bg-red-500 text-white font-bold py-3 px-4 rounded-xl transition">
                    Validar 2FA
                </button>
            </form>
        </div>
    </div>
</div>
</x-guest-layout>
