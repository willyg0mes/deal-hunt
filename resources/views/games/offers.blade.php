@extends('layouts.app')

@section('content')
<div class="min-h-screen w-full overflow-x-hidden">
    <div class="max-w-7xl mx-auto px-6 py-8">
        <!-- Breadcrumb -->
        <div class="mb-6">
            <nav class="flex text-sm text-gray-400" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="hover:text-white transition">Home</a>
                <span class="mx-2">/</span>
                <span class="text-white">{{ $game->title }}</span>
            </nav>
        </div>

        <h1 class="text-4xl font-bold text-white mb-8">🎯 Ofertas Relacionadas</h1>

        {{-- Card principal do jogo --}}
        <div class="bg-gradient-to-r from-gray-900 via-gray-800 to-gray-700 rounded-2xl shadow-lg p-8 mb-8 w-full">
            <div class="flex flex-col lg:flex-row items-center gap-8">
                <div class="relative flex-shrink-0 w-full max-w-md game-cover overflow-hidden rounded-xl shadow-lg bg-gray-950">
                    <img src="{{ $game->optimized_image }}" alt="{{ $game->title }}" class="absolute inset-0 w-full h-full object-cover object-center" />
                    @if($game->discount_percent > 0)
                    <div class="absolute top-2 right-2 bg-red-600 text-white text-sm font-bold px-3 py-1 rounded-lg shadow-lg">
                        -{{ $game->discount_percent }}%
                    </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0 text-center lg:text-left">
                    <h2 class="text-3xl font-bold text-white mb-4">{{ $game->title }}</h2>
                <!-- Categorias -->
                @if($game->categories->count() > 0)
                <div class="flex flex-wrap gap-2 mb-4 justify-center lg:justify-start">
                    @foreach($game->categories->take(4) as $category)
                        <span class="text-xs bg-gray-700 text-gray-300 px-3 py-1 rounded-full">
                            {{ $category->name }}
                        </span>
                    @endforeach
                    @if($game->categories->count() > 4)
                        <span class="text-xs text-gray-400">+{{ $game->categories->count() - 4 }}</span>
                    @endif
                </div>
                @endif
                
                <div class="flex items-center gap-2 mb-4 justify-center lg:justify-start">
                    <span class="text-gray-400">Disponível na:</span>
                    <span class="text-sm font-bold px-3 py-1 rounded {{ $game->store_color }} text-white">
                        {{ $game->store_name }}
                    </span>
                </div>
                <div class="flex items-center gap-4 mb-4 justify-center lg:justify-start">
                    <span class="text-2xl font-bold text-green-400"><x-price :price="$game->sale_price" /></span>
                    @if($game->normal_price > $game->sale_price)
                    <span class="text-lg text-gray-400 line-through"><x-price :price="$game->normal_price" /></span>
                    @endif
                </div>
                @if($game->deal_ends_at)
                <div class="text-sm text-orange-400 mb-4 justify-center lg:justify-start flex items-center gap-2">
                    @php
                        $dealEndsAt = $game->deal_ends_at instanceof \Carbon\Carbon 
                            ? $game->deal_ends_at 
                            : \Carbon\Carbon::parse($game->deal_ends_at);
                    @endphp
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Oferta expira em: {{ $dealEndsAt->format('d/m/Y H:i') }}
                    @if($dealEndsAt->isToday())
                        <span class="text-red-400 font-bold">(Hoje!)</span>
                    @elseif($dealEndsAt->isTomorrow())
                        <span class="text-yellow-400 font-bold">(Amanhã)</span>
                    @elseif($dealEndsAt->isFuture())
                        @php
                            $daysLeft = max(1, ceil(now()->diffInDays($dealEndsAt)));
                        @endphp
                        @if($daysLeft <= 7)
                            <span class="text-yellow-400 font-bold">({{ $daysLeft }} {{ $daysLeft == 1 ? 'dia' : 'dias' }})</span>
                        @endif
                    @elseif($dealEndsAt->isPast())
                        <span class="text-red-500 font-bold">(Expirada)</span>
                    @endif
                </div>
                @endif
                <div class="flex flex-col sm:flex-row gap-3 mt-4">
                    <a href="{{ $game->best_url }}" target="_blank" rel="noopener noreferrer"
                        class="bg-red-600 hover:bg-red-700 text-white font-semibold px-6 py-3 rounded-lg shadow transition text-center flex items-center justify-center gap-2">
                        Ver na {{ $game->store_name }}
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M14 3h7v7m0 0L10 21l-7-7 11-11z"/>
                        </svg>
                    </a>
                    <a href="{{ route('games.show', $game) }}"
                        class="bg-gray-700 hover:bg-gray-600 text-white font-semibold px-6 py-3 rounded-lg shadow transition text-center">
                        Ver Detalhes
                    </a>
                </div>
            </div>
        </div>

        {{-- Outras ofertas --}}
        @if($offers->count() > 0)
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-white mb-4">🎲 Outras Ofertas Relacionadas</h2>
            <p class="text-gray-400 mb-6">Confira outros jogos que podem interessar você</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 w-full">
            @foreach($offers as $offer)
                <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 group w-full">
                    <!-- Imagem com altura fixa -->
                    <div class="relative game-cover w-full overflow-hidden bg-gray-950">
                        <img src="{{ $offer->optimized_image }}" alt="{{ $offer->title }}" class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-300" />
                        @if($offer->discount_percent > 0)
                        <div class="absolute top-3 left-3">
                            <span class="bg-red-600 text-white text-xs font-bold px-2 py-1 rounded-full shadow">
                                -{{ $offer->discount_percent }}%
                            </span>
                        </div>
                        @endif
                        <!-- Badge da loja no topo direito -->
                        <div class="absolute top-3 right-3">
                            <span class="text-xs font-bold px-2 py-1 rounded {{ $offer->store_color }} text-white shadow">
                                {{ $offer->store_name }}
                            </span>
                        </div>
                    </div>
                
                <!-- Conteúdo -->
                <div class="p-5">
                    <!-- Título -->
                    <h3 class="text-lg font-bold text-white mb-3 h-12 flex items-start overflow-hidden">
                        <span class="line-clamp-2 leading-tight group-hover:text-red-400 transition-colors">{{ $offer->title }}</span>
                    </h3>
                    
                    <!-- Categorias -->
                    @if($offer->categories->count() > 0)
                    <div class="flex flex-wrap gap-1 mb-3">
                        @foreach($offer->categories->take(3) as $category)
                            <span class="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded">
                                {{ $category->name }}
                            </span>
                        @endforeach
                        @if($offer->categories->count() > 3)
                            <span class="text-xs text-gray-400">+{{ $offer->categories->count() - 3 }}</span>
                        @endif
                    </div>
                    @endif
                    
                    <!-- Preços -->
                    <div class="flex items-center gap-3 mb-3">
                        <span class="text-xl font-bold text-green-400"><x-price :price="$offer->sale_price" /></span>
                        @if($offer->normal_price > $offer->sale_price)
                        <span class="text-sm text-gray-400 line-through"><x-price :price="$offer->normal_price" /></span>
                        @endif
                    </div>
                    
                    <!-- Data de expiração -->
                    @if($offer->deal_ends_at)
                    <div class="text-xs text-orange-400 mb-3 flex items-center gap-1">
                        @php
                            $offerEndsAt = $offer->deal_ends_at instanceof \Carbon\Carbon 
                                ? $offer->deal_ends_at 
                                : \Carbon\Carbon::parse($offer->deal_ends_at);
                        @endphp
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Expira: {{ $offerEndsAt->format('d/m/y') }}
                        @if($offerEndsAt->isToday())
                            <span class="text-red-400 font-bold">(Hoje!)</span>
                        @elseif($offerEndsAt->isTomorrow())
                            <span class="text-yellow-400 font-bold">(Amanhã)</span>
                        @elseif($offerEndsAt->isFuture())
                            @php
                                $daysLeft = max(1, ceil(now()->diffInDays($offerEndsAt)));
                            @endphp
                            @if($daysLeft <= 7)
                                <span class="text-yellow-400 font-bold">({{ $daysLeft }} {{ $daysLeft == 1 ? 'dia' : 'dias' }})</span>
                            @endif
                        @elseif($offerEndsAt->isPast())
                            <span class="text-red-500 font-bold">(Expirada)</span>
                        @endif
                    </div>
                    @endif
                    
                    <!-- Botão -->
                    <a href="{{ route('games.show', $offer) }}"
                       class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded-lg shadow transition text-center block">
                        Ver Oferta
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @else
            <div class="text-center py-16">
                <div class="text-6xl mb-4">🎯</div>
                <h3 class="text-2xl font-bold text-white mb-2">Nenhuma oferta relacionada encontrada</h3>
                <p class="text-gray-400 mb-6">Não conseguimos encontrar ofertas similares para este jogo no momento.</p>
                <a href="{{ route('home') }}" class="bg-red-600 hover:bg-red-700 text-white font-semibold px-6 py-3 rounded-lg transition inline-block">
                    Voltar para todas as ofertas
                </a>
            </div>
        @endif
    </div>
</div>
@endsection