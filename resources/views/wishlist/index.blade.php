@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
    <div class="container mx-auto px-4 py-8">
        <!-- Cabeçalho melhorado -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-8 h-8 mr-3 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"/>
                        </svg>
                        Meus Jogos de Interesse
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        @if($games->isEmpty())
                            Adicione jogos à sua lista para acompanhar ofertas
                        @else
                            {{ $games->count() }} {{ $games->count() === 1 ? 'jogo' : 'jogos' }} na sua lista
                        @endif
                    </p>
                </div>
                @if(!$games->isEmpty())
                <div class="text-right">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Valor total estimado</div>
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                        <x-price :price="$games->sum('sale_price')" />
                    </div>
                </div>
                @endif
            </div>
        </div>

        @if($games->isEmpty())
            <!-- Estado vazio melhorado -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-12 text-center">
                <div class="max-w-md mx-auto">
                    <svg class="w-24 h-24 mx-auto text-gray-400 dark:text-gray-500 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                        Sua lista está vazia
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Explore nossos jogos e adicione os que mais interessam para acompanhar as melhores ofertas.
                    </p>
                    <a href="{{ route('games.index') }}" class="inline-flex items-center px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Explorar Jogos
                    </a>
                </div>
            </div>
        @else
            <!-- Lista de jogos melhorada -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($games as $game)
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                        <!-- Imagem do jogo -->
                        <div class="relative">
                            <img src="{{ $game->image }}" alt="{{ $game->title }}" class="w-full h-48 object-cover">
                            
                            <!-- Badge de desconto -->
                            @if($game->discount_percent > 0)
                                <div class="absolute top-3 left-3 bg-red-500 text-white px-2 py-1 rounded-full text-xs font-bold">
                                    -{{ $game->discount_percent }}%
                                </div>
                            @endif
                            
                            <!-- Badge da loja -->
                            <div class="absolute top-3 right-3 {{ $game->store_color }} text-white px-2 py-1 rounded-full text-xs font-medium">
                                {{ $game->store_name }}
                            </div>
                        </div>

                        <!-- Conteúdo do card -->
                        <div class="p-5">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 line-clamp-2">
                                {{ $game->title }}
                            </h3>
                            
                            <!-- Preços -->
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex flex-col">
                                    <div class="text-xl font-bold text-green-600 dark:text-green-400">
                                        <x-price :price="$game->sale_price" />
                                    </div>
                                    @if($game->normal_price > $game->sale_price)
                                        <div class="text-sm text-gray-500 line-through">
                                            <x-price :price="$game->normal_price" />
                                        </div>
                                    @endif
                                </div>
                                @if($game->deal_ends_at)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        <div>Expira em:</div>
                                        <div class="font-medium">{{ $game->deal_ends_at->diffForHumans() }}</div>
                                    </div>
                                @endif
                            </div>

                            <!-- Ações -->
                            <div class="flex space-x-2">
                                <a href="{{ route('games.show', $game) }}" 
                                   class="flex-1 bg-blue-500 hover:bg-blue-600 text-white text-center py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                                    Ver Detalhes
                                </a>
                                
                                <form action="{{ route('wishlist.toggle', $game) }}" method="POST" class="wishlist-form" data-game-id="{{ $game->id }}">
                                    @csrf
                                    <button type="submit" 
                                            class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-lg transition-colors wishlist-btn" 
                                            data-in-wishlist="true"
                                            title="Remover da lista">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                            <path fill-rule="evenodd" d="M4 5a1 1 0 011-1h10a1 1 0 011 1v1H4V5zM3 10a1 1 0 011-1h12a1 1 0 011 1v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6z"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Estatísticas da lista -->
            <div class="mt-8 bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Estatísticas da sua Lista</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $games->count() }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Total de Jogos</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                            <x-price :price="$games->sum('sale_price')" />
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Valor Total</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                            <x-price :price="$games->sum('normal_price') - $games->sum('sale_price')" />
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Economia Total</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                            {{ round($games->avg('discount_percent')) }}%
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Desconto Médio</div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endpush
@endsection
