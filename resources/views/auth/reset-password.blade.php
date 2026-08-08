<x-guest-layout title="Redefinir senha | {{ config('app.name', 'DealHunt') }}">
    <div class="rounded-2xl border border-red-800/50 bg-gray-950/80 p-6 sm:p-8 space-y-5">
        <h1 class="text-2xl font-bold text-red-400 text-center">Redefinir senha</h1>
        <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">
            <div>
                <x-input-label for="email" value="Email" class="text-gray-300" />
                <x-text-input id="email" class="block mt-1 w-full bg-gray-900 border-red-800/70 text-gray-100 focus:border-red-500 focus:ring-red-500" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-400" />
            </div>
            <div>
                <x-input-label for="password" value="Nova senha" class="text-gray-300" />
                <x-text-input id="password" class="block mt-1 w-full bg-gray-900 border-red-800/70 text-gray-100 focus:border-red-500 focus:ring-red-500" type="password" name="password" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-400" />
            </div>
            <div>
                <x-input-label for="password_confirmation" value="Confirme a senha" class="text-gray-300" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full bg-gray-900 border-red-800/70 text-gray-100 focus:border-red-500 focus:ring-red-500" type="password" name="password_confirmation" required autocomplete="new-password" />
            </div>
            <button type="submit" class="w-full bg-red-700 hover:bg-red-600 text-white font-bold py-2.5 px-4 rounded-lg transition">
                Salvar nova senha
            </button>
        </form>
    </div>
</x-guest-layout>
