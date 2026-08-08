<x-guest-layout title="Confirmar senha | {{ config('app.name', 'DealHunt') }}">
    <div class="rounded-2xl border border-red-800/50 bg-gray-950/80 p-6 sm:p-8 space-y-5">
        <p class="text-sm text-gray-300 text-center">
            Área segura. Confirme sua senha para continuar.
        </p>
        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
            @csrf
            <div>
                <x-input-label for="password" value="Senha" class="text-gray-300" />
                <x-text-input id="password" class="block mt-1 w-full bg-gray-900 border-red-800/70 text-gray-100 focus:border-red-500 focus:ring-red-500" type="password" name="password" required autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-400" />
            </div>
            <button type="submit" class="w-full bg-red-700 hover:bg-red-600 text-white font-bold py-2.5 px-4 rounded-lg transition">
                Confirmar
            </button>
        </form>
    </div>
</x-guest-layout>
