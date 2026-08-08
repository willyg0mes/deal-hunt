<x-guest-layout title="Recuperar senha | {{ config('app.name', 'DealHunt') }}">
    <div class="rounded-2xl border border-red-800/50 bg-gray-950/80 backdrop-blur-sm shadow-2xl shadow-black/40 p-6 sm:p-8">
        <h1 class="text-2xl font-bold text-red-400 mb-6 text-center">Recuperar senha</h1>
        @if (session('status'))
            <div class="mb-4 text-green-400 font-semibold text-center text-sm">
                {{ session('status') }}
            </div>
        @endif
        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf
            <div>
                <x-input-label for="email" value="Email" class="text-gray-300" />
                <x-text-input id="email" class="block mt-1 w-full bg-gray-900 border-red-800/70 text-gray-100 focus:border-red-500 focus:ring-red-500" type="email" name="email" required autofocus />
                <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-400" />
            </div>
            <button type="submit" class="w-full bg-red-700 hover:bg-red-600 text-white font-bold py-2.5 px-4 rounded-lg transition">
                Enviar link de recuperação
            </button>
        </form>
        <div class="mt-6 text-center text-sm">
            <a href="{{ route('login') }}" class="text-red-400 hover:text-red-300 underline underline-offset-2">Voltar ao login</a>
        </div>
    </div>
</x-guest-layout>
