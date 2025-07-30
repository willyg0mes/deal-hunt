@extends('layouts.app')

@section('content')
<div class="w-full h-full">
    <div class="p-6 max-w-none">

        {{-- Slide Top Descontos --}}
        <div class="mb-10 w-full">
            <div class="swiper mySwiper h-72 sm:h-96 md:h-[28rem] rounded-2xl w-full overflow-hidden">
                <div class="swiper-wrapper h-full rounded-2xl">
                    @foreach($topGames as $game)
                    <div class="swiper-slide flex items-center justify-center h-full">
                        <div class="relative w-full h-full rounded-2xl overflow-hidden shadow-2xl flex items-end">
                            <img src="{{ $game->image }}" alt="{{ $game->title }}"
                                class="absolute inset-0 w-full h-full object-cover z-0" />
                            {{-- Desconto geométrico no topo direito --}}
                            <div class="absolute top-4 right-0 z-20">
                                <div class="discount-tag flex items-center justify-center px-6 py-2">
                                    <span class="text-white font-bold text-lg drop-shadow">-{{ $game->discount_percent }}%</span>
                                </div>
                            </div>
                            <div class="relative z-10 p-8 w-full flex flex-col items-start bg-black/70 dark:bg-black/80 rounded-2xl">
                                <h2 class="text-2xl font-bold text-white drop-shadow mb-2">{{ $game->title }}</h2>
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="text-2xl font-bold text-green-400 drop-shadow">
                                        <x-price :price="$game->sale_price" />
                                    </span>
                                    @if($game->normal_price > $game->sale_price)
                                        <span class="text-lg text-gray-300 line-through">
                                            <x-price :price="$game->normal_price" />
                                        </span>
                                    @endif
                                </div>
                                <a href="{{ route('games.show', $game) }}"
                                    class="mt-4 bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800 text-white font-semibold px-6 py-2 rounded shadow transition">Ver Oferta</a>
                            </div>
                        </div>
                </div>
                @endforeach
            </div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
            <div class="swiper-pagination"></div>
        </div>
    </div>

    {{-- Filtros, busca e listagem dinâmica --}}
            {{-- Filtros de Busca --}}
        <div x-data="{ 
            search: '', 
            store: '', 
            min_discount: '', 
            category: '', 
            loading: false,
            updateFilters() {
                this.loading = true;
                const params = new URLSearchParams();
                if (this.search) params.append('search', this.search);
                if (this.store) params.append('store', this.store);
                if (this.min_discount) params.append('min_discount', this.min_discount);
                if (this.category) params.append('category', this.category);
                
                fetch(`/?${params.toString()}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    document.getElementById('games-list').innerHTML = html;
                    this.loading = false;
                })
                .catch(error => {
                    console.error('Erro na busca:', error);
                    this.loading = false;
                });
            }
        }" class="mb-8 w-full">
            <!-- Filtros de Busca -->
            <div class="bg-gray-800 dark:bg-gray-900 rounded-lg p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Busca -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 dark:text-gray-400 mb-1">Buscar Jogo</label>
                        <input 
                            x-model="search" 
                            @input.debounce.500="updateFilters()" 
                            type="text"
                            placeholder="Digite o nome do jogo..."
                            class="w-full bg-gray-700 dark:bg-gray-800 border border-gray-600 dark:border-gray-700 rounded-md px-3 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent" />
                    </div>

                    <!-- Categoria -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 dark:text-gray-400 mb-1">Categoria</label>
                        <select 
                            x-model="category" 
                            @change="updateFilters()"
                            class="w-full bg-gray-700 dark:bg-gray-800 border border-gray-600 dark:border-gray-700 rounded-md px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                            <option value="">Todas as categorias</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->name }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Loja -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 dark:text-gray-400 mb-1">Loja</label>
                        <select 
                            x-model="store" 
                            @change="updateFilters()"
                            class="w-full bg-gray-700 dark:bg-gray-800 border border-gray-600 dark:border-gray-700 rounded-md px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                            <option value="">Todas as lojas</option>
                            @foreach($storeNames as $storeName)
                                <option value="{{ $storeName }}">{{ $storeName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Desconto Mínimo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 dark:text-gray-400 mb-1">Desconto Mínimo</label>
                        <input 
                            x-model="min_discount" 
                            @input.debounce.500="updateFilters()" 
                            type="number"
                            min="0" 
                            max="100" 
                            placeholder="Desconto mínimo (%)"
                            class="w-full bg-gray-700 dark:bg-gray-800 border border-gray-600 dark:border-gray-700 rounded-md px-3 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent" />
                    </div>
                </div>
                
                <!-- Indicador de loading -->
                <div x-show="loading" class="flex items-center justify-center gap-2 text-white mt-4">
                    <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-sm">Buscando...</span>
                </div>
            </div>
        </div>

        {{-- Lista paginada dinâmica --}}
        <div id="games-list" class="w-full">
            @include('partials.games-list', ['games' => $games])
        </div>
    </div>
</div>

{{-- SwiperJS --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css" />
<script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        new Swiper('.mySwiper', {
            slidesPerView: 1,
            spaceBetween: 20,
            loop: true,
            autoplay: {
                delay: 3500,
                disableOnInteraction: false,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            }
        });
    });
</script>

<style>
.discount-tag {
    background: var(--color-primary);
    color: #fff;
    min-width: 70px;
    min-height: 38px;
    position: relative;
    clip-path: polygon(18% 0, 100% 0, 100% 100%, 18% 100%, 0 50%);
    box-shadow: 0 2px 8px rgba(0,0,0,0.18);
    font-family: inherit;
    font-weight: bold;
    letter-spacing: 1px;
}

.discount-tag::before {
    content: '';
    display: block;
    position: absolute;
    left: 8px;
    top: 50%;
    transform: translateY(-50%);
    width: 8px;
    height: 8px;
    background: #fff;
    border-radius: 50%;
}

/* Estilos para line-clamp */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

    </div>
</div>
@endsection