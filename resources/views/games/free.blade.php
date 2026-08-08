@extends('layouts.app')

@section('title', 'Jogos grátis agora | '.config('app.name', 'DealHunt'))
@section('meta_description', 'Lista de jogos gratuitos e free-to-play atualizada. Pegue grátis no DealHunt.')
@section('canonical', route('games.free'))

@section('content')
<div class="px-6 py-8">
    <!-- Cabeçalho -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-white mb-2">🎮 Jogos Gratuitos Permanentes</h1>
        <p class="text-gray-300">Descubra jogos incríveis que são gratuitos para sempre! Sem pegadinhas, sem tempo limite.</p>
    </div>

    <!-- Filtros -->
    <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl p-6 mb-8">
        <form method="GET" action="{{ route('games.free') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-300 mb-2">Buscar</label>
                <input type="text" id="search" name="search" value="{{ request('search') }}" 
                       placeholder="Nome do jogo..." 
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-red-500 focus:border-transparent">
            </div>
            
            <div>
                <label for="category" class="block text-sm font-medium text-gray-300 mb-2">Categoria</label>
                <select id="category" name="category" 
                        class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    <option value="">Todas as categorias</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->slug }}" {{ request('category') == $category->slug ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label for="store" class="block text-sm font-medium text-gray-300 mb-2">Loja</label>
                <select id="store" name="store" 
                        class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    <option value="">Todas as lojas</option>
                    @foreach($stores as $store)
                        <option value="{{ $store }}" {{ request('store') == $store ? 'selected' : '' }}>
                            {{ $store }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex items-end gap-2">
                <button type="submit" 
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold px-6 py-2 rounded-lg transition-colors duration-200">
                    Buscar
                </button>
                @if(request()->hasAny(['search', 'category', 'store']))
                    <a href="{{ route('games.free') }}" 
                       class="bg-gray-600 hover:bg-gray-700 text-white font-semibold px-4 py-2 rounded-lg transition-colors duration-200">
                        Limpar
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Estatísticas -->
    <div class="mb-6">
        <p class="text-gray-300">
            <span class="font-semibold text-white">{{ $games->total() }}</span> jogos gratuitos encontrados
            @if(request()->hasAny(['search', 'category', 'store']))
                @if(request('search'))
                    para "<span class="text-red-400">{{ request('search') }}</span>"
                @endif
                @if(request('category'))
                    na categoria "<span class="text-red-400">{{ $categories->where('slug', request('category'))->first()->name ?? request('category') }}</span>"
                @endif
                @if(request('store'))
                    na loja "<span class="text-red-400">{{ request('store') }}</span>"
                @endif
            @endif
        </p>
    </div>

    <!-- Grid de Jogos -->
    @if($games->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
            @foreach($games as $game)
                <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 group">
                    <div class="relative aspect-video overflow-hidden">
                        <img src="{{ $game->image }}" alt="{{ $game->title }}" 
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                        
                        <!-- Badge Gratuito -->
                        <div class="absolute top-3 left-3">
                            <span class="bg-green-600 text-white text-xs font-bold px-2 py-1 rounded-full">
                                GRÁTIS
                            </span>
                        </div>
                        
                        <!-- Loja no canto superior direito -->
                        <div class="absolute top-3 right-3">
                            <span class="bg-black/70 text-white text-xs font-medium px-2 py-1 rounded">
                                {{ $game->store_name }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="p-4">
                        <h3 class="font-bold text-white mb-2 line-clamp-2 group-hover:text-red-400 transition-colors">
                            {{ $game->title }}
                        </h3>
                        
                        @if($game->categories->count() > 0)
                            <div class="flex flex-wrap gap-1 mb-3">
                                @foreach($game->categories->take(2) as $category)
                                    <span class="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded">
                                        {{ $category->name }}
                                    </span>
                                @endforeach
                                @if($game->categories->count() > 2)
                                    <span class="text-xs text-gray-400">+{{ $game->categories->count() - 2 }}</span>
                                @endif
                            </div>
                        @endif
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-2xl font-bold text-green-400">
                                    GRÁTIS
                                </div>
                                @if($game->free_since)
                                    <p class="text-xs text-gray-400">
                                        Gratuito desde {{ $game->free_since->format('d/m/Y') }}
                                    </p>
                                @endif
                            </div>
                            <a href="{{ route('games.show', $game) }}" 
                               class="bg-red-600 hover:bg-red-700 text-white font-medium px-4 py-2 rounded-lg transition-colors duration-200 text-sm">
                                Ver Detalhes
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Paginação -->
        <div class="flex justify-center">
            {{ $games->appends(request()->query())->links() }}
        </div>
    @else
        <!-- Estado vazio -->
        <div class="text-center py-16">
            <div class="text-6xl mb-4">🎮</div>
            <h3 class="text-2xl font-bold text-white mb-2">Nenhum jogo gratuito encontrado</h3>
            <p class="text-gray-400 mb-6">
                @if(request()->hasAny(['search', 'category', 'store']))
                    Tente ajustar os filtros ou fazer uma nova busca.
                @else
                    No momento não há jogos gratuitos disponíveis.
                @endif
            </p>
            @if(request()->hasAny(['search', 'category', 'store']))
                <a href="{{ route('games.free') }}" 
                   class="bg-red-600 hover:bg-red-700 text-white font-semibold px-6 py-3 rounded-lg transition-colors duration-200">
                    Ver Todos os Jogos Gratuitos
                </a>
            @endif
        </div>
    @endif
</div>
@endsection
