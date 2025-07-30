@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header com filtros -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white mb-6">Todos os Jogos</h1>
        
        <!-- Filtros -->
        <form method="GET" action="{{ route('games.index') }}" class="bg-gray-800 rounded-lg p-4 mb-6" id="filters-form">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Busca -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-300 mb-1">Buscar Jogo</label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" 
                           class="w-full bg-gray-700 border border-gray-600 rounded-md px-3 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                           placeholder="Digite o nome do jogo...">
                </div>
                
                <!-- Categoria -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-300 mb-1">Categoria</label>
                    <select id="category" name="category" 
                            class="w-full bg-gray-700 border border-gray-600 rounded-md px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                        <option value="">Todas as categorias</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" {{ request('category') == $category->slug ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Loja -->
                <div>
                    <label for="store" class="block text-sm font-medium text-gray-300 mb-1">Loja</label>
                    <select id="store" name="store" 
                            class="w-full bg-gray-700 border border-gray-600 rounded-md px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                        <option value="">Todas as lojas</option>
                        @foreach($stores as $store)
                            <option value="{{ $store }}" {{ request('store') == $store ? 'selected' : '' }}>
                                {{ $store }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Botões -->
                <div class="flex items-end gap-2">
                    <button type="submit" id="filter-btn"
                            class="flex-1 bg-red-600 hover:bg-red-700 text-white font-medium px-4 py-2 rounded-md transition">
                        <span class="filter-text">Filtrar</span>
                        <span class="filter-loading hidden">
                            <svg class="animate-spin h-4 w-4 inline" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Carregando...
                        </span>
                    </button>
                    <a href="{{ route('games.index') }}" 
                       class="bg-gray-600 hover:bg-gray-700 text-white font-medium px-4 py-2 rounded-md transition text-center">
                        Limpar
                    </a>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Resultados -->
    <div id="games-results">
        @if($games->count() > 0)
            <div class="mb-6">
                <p class="text-gray-400">
                    Mostrando {{ $games->firstItem() }}-{{ $games->lastItem() }} de {{ $games->total() }} resultados
                    @if(request('search'))
                        para "<strong class="text-white">{{ request('search') }}</strong>"
                    @endif
                </p>
            </div>
            
            <!-- Grid de jogos -->
            @include('partials.games-list', ['games' => $games])
            
            <!-- Paginação -->
            <div class="mt-8">
                {{ $games->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-300">Nenhum jogo encontrado</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if(request()->hasAny(['search', 'category', 'store']))
                        Tente ajustar os filtros ou fazer uma nova busca.
                    @else
                        Não há jogos disponíveis no momento.
                    @endif
                </p>
                @if(request()->hasAny(['search', 'category', 'store']))
                    <div class="mt-6">
                        <a href="{{ route('games.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                            Ver todos os jogos
                        </a>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
