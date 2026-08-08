@extends('layouts.app')

@section('title', 'Jogos grátis agora | '.config('app.name', 'DealHunt'))
@section('meta_description', 'Lista de jogos gratuitos e free-to-play atualizada. Pegue grátis no DealHunt.')
@section('canonical', route('games.free'))

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Cabeçalho -->
    <div class="mb-6 sm:mb-8">
        <h1 class="text-3xl sm:text-4xl font-bold text-white dark:text-white mb-2">Jogos Gratuitos Permanentes</h1>
        <p class="text-gray-300 dark:text-gray-400">Jogos que são gratuitos para sempre. Sem tempo limite, sem pegadinhas.</p>
    </div>

    <!-- Filtros (mesmo padrão da listagem principal) -->
    <div class="bg-gray-800/50 dark:bg-gray-800/50 backdrop-blur-sm rounded-xl p-3 sm:p-4 lg:p-6 mb-4 sm:mb-6">
        <form method="GET" action="{{ route('games.free') }}" id="filters-form" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 lg:gap-4">
            <div class="sm:col-span-2 lg:col-span-1">
                <label for="search" class="block text-xs sm:text-sm font-medium text-gray-300 dark:text-gray-400 mb-0.5 sm:mb-1">Buscar</label>
                <input type="text" id="search" name="search" value="{{ request('search') }}" 
                       placeholder="Nome do jogo..." 
                       class="w-full px-2.5 sm:px-3 lg:px-4 py-1.5 sm:py-2 bg-gray-700 dark:bg-gray-700 border border-gray-600 dark:border-gray-600 rounded-lg text-sm sm:text-base text-white dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-red-500 focus:border-transparent">
            </div>
            <div>
                <label for="category" class="block text-xs sm:text-sm font-medium text-gray-300 dark:text-gray-400 mb-0.5 sm:mb-1">Categoria</label>
                <select id="category" name="category" 
                        class="w-full px-2.5 sm:px-3 lg:px-4 py-1.5 sm:py-2 bg-gray-700 dark:bg-gray-700 border border-gray-600 dark:border-gray-600 rounded-lg text-sm sm:text-base text-white dark:text-white focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    <option value="">Todas</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->slug }}" {{ request('category') == $category->slug ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="store" class="block text-xs sm:text-sm font-medium text-gray-300 dark:text-gray-400 mb-0.5 sm:mb-1">Loja</label>
                <select id="store" name="store" 
                        class="w-full px-2.5 sm:px-3 lg:px-4 py-1.5 sm:py-2 bg-gray-700 dark:bg-gray-700 border border-gray-600 dark:border-gray-600 rounded-lg text-sm sm:text-base text-white dark:text-white focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    <option value="">Todas</option>
                    @foreach($stores as $store)
                        <option value="{{ $store }}" {{ request('store') == $store ? 'selected' : '' }}>
                            {{ $store }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-1">
                <button type="submit" id="filter-btn"
                        class="flex-1 bg-red-600 hover:bg-red-700 dark:bg-red-600 dark:hover:bg-red-700 text-white font-semibold px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg transition-colors text-sm sm:text-base">
                    Filtrar
                </button>
                @if(request()->hasAny(['search', 'category', 'store']))
                    <a href="{{ route('games.free') }}" 
                       class="bg-gray-600 hover:bg-gray-700 dark:bg-gray-600 dark:hover:bg-gray-700 text-white font-semibold px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg transition-colors text-sm sm:text-base">
                        Limpar
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Resultados: usa o mesmo partial da listagem principal -->
    <div id="games-results">
        @if($games->count() > 0)
            @include('partials.games-list', ['games' => $games])
        @else
            <div class="text-center py-16">
                <div class="text-6xl mb-4">🎮</div>
                <h3 class="text-2xl font-bold text-white dark:text-white mb-2">Nenhum jogo gratuito encontrado</h3>
                <p class="text-gray-400 dark:text-gray-400 mb-6">
                    @if(request()->hasAny(['search', 'category', 'store']))
                        Tente ajustar os filtros ou <a href="{{ route('games.free') }}" class="text-red-400 hover:underline">ver todos</a>.
                    @else
                        Ainda não há jogos gratuitos no catálogo. No painel admin, use <strong>Buscar Jogos Gratuitos</strong> para importar da RAWG.
                    @endif
                </p>
                @if(request()->hasAny(['search', 'category', 'store']))
                    <a href="{{ route('games.free') }}" 
                       class="inline-block bg-red-600 hover:bg-red-700 dark:bg-red-600 dark:hover:bg-red-700 text-white font-semibold px-6 py-3 rounded-lg transition-colors">
                        Ver todos os jogos gratuitos
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
