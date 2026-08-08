@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
    <h1 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6 text-gray-900 dark:text-gray-100">🔔 Minhas Notificações</h1>

    @if(session('status'))
        <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-200 px-4 py-3 rounded mb-4 text-sm sm:text-base">
            {{ session('status') }}
        </div>
    @endif

    @if ($notifications->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-8 text-center">
            <div class="text-5xl mb-4">🔔</div>
            <p class="text-gray-600 dark:text-gray-400 text-sm sm:text-base">Você ainda não recebeu nenhuma notificação.</p>
        </div>
    @else
        <ul class="space-y-3 sm:space-y-4">
            @foreach ($notifications as $notification)
                <li class="bg-white dark:bg-gray-800 shadow-sm hover:shadow-md dark:shadow-gray-900/50 rounded-lg p-4 sm:p-5 border border-gray-200 dark:border-gray-700 transition-shadow">
                    <!-- Layout Mobile: Vertical -->
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3 sm:gap-4">
                        <!-- Conteúdo da notificação -->
                        <div class="flex-1 min-w-0">
                            <p class="{{ $notification->read ? 'text-gray-500 dark:text-gray-400' : 'text-gray-900 dark:text-gray-100 font-semibold' }} text-sm sm:text-base break-words">
                                {{ $notification->message }}
                            </p>
                            <small class="text-gray-400 dark:text-gray-500 block mt-1 text-xs sm:text-sm">
                                {{ $notification->created_at->diffForHumans() }}
                            </small>
                            @if ($notification->game)
                                <a href="{{ route('games.show', $notification->game) }}" class="inline-block mt-2 text-xs sm:text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 hover:underline font-medium">
                                    🎮 Ver Jogo
                                </a>
                            @endif
                        </div>
                        
                        <!-- Botões de ação -->
                        <div class="flex flex-row sm:flex-col gap-2 items-stretch sm:items-end flex-shrink-0">
                            @if (!$notification->read)
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="flex-1 sm:flex-none">
                                    @csrf
                                    <button class="w-full sm:w-auto text-xs sm:text-sm text-white bg-green-500 hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-700 px-3 sm:px-4 py-2 rounded transition-colors whitespace-nowrap">
                                        ✓ Marcar lida
                                    </button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" class="flex-1 sm:flex-none">
                                @csrf
                                @method('DELETE')
                                <button class="w-full sm:w-auto text-xs sm:text-sm text-white bg-red-500 hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-700 px-3 sm:px-4 py-2 rounded transition-colors whitespace-nowrap">
                                    🗑️ Excluir
                                </button>
                            </form>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
