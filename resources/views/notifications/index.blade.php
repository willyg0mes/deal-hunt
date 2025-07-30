@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-xl font-bold mb-6">🔔 Minhas Notificações</h1>

    @if(session('status'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4">
            {{ session('status') }}
        </div>
    @endif

    @if ($notifications->isEmpty())
        <p class="text-gray-600">Você ainda não recebeu nenhuma notificação.</p>
    @else
        <ul class="space-y-3">
            @foreach ($notifications as $notification)
                <li class="bg-white shadow rounded p-4 flex justify-between items-start">
                    <div class="flex-1">
                        <p class="{{ $notification->read ? 'text-gray-500' : 'text-black font-semibold' }}">
                            {{ $notification->message }}
                        </p>
                        <small class="text-gray-400 block">
                            {{ $notification->created_at->diffForHumans() }}
                        </small>
                        @if ($notification->game)
                            <a href="{{ route('games.show', $notification->game) }}" class="text-sm text-blue-600 hover:underline">Ver Jogo</a>
                        @endif
                    </div>
                    <div class="flex flex-col gap-2 items-end ml-4">
                        @if (!$notification->read)
                            <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                @csrf
                                <button class="text-xs text-white bg-green-500 hover:bg-green-600 px-2 py-1 rounded">Marcar como lida</button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}">
                            @csrf
                            @method('DELETE')
                            <button class="text-xs text-white bg-red-500 hover:bg-red-600 px-2 py-1 rounded">Excluir</button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
