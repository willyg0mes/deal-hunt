<x-guest-layout title="Verificar email | {{ config('app.name', 'DealHunt') }}">
    <div class="rounded-2xl border border-red-800/50 bg-gray-950/80 p-6 sm:p-8 space-y-5">
        <h1 class="text-2xl font-bold text-red-400 text-center">Verifique seu email</h1>
        <p class="text-sm text-gray-300 text-center">
            Enviamos um link de verificação. Se não chegou, podemos reenviar.
        </p>

        @if (session('status') == 'verification-link-sent')
            <div class="text-sm text-green-400 text-center font-medium">
                Novo link enviado para o seu email.
            </div>
        @endif

        <div class="flex flex-col sm:flex-row gap-3 items-center justify-between">
            <form method="POST" action="{{ route('verification.send') }}" class="w-full sm:w-auto">
                @csrf
                <button type="submit" class="w-full bg-red-700 hover:bg-red-600 text-white font-bold py-2.5 px-4 rounded-lg transition">
                    Reenviar email
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-gray-400 hover:text-white underline underline-offset-2">
                    Sair
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
