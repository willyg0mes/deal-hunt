@extends('layouts.app')

@section('title', 'DealHunt — ofertas de jogos em tempo real')
@section('meta_description', 'Encontre as melhores ofertas de games na Steam, Epic e outras lojas. Wishlist, alertas e descontos atualizados no DealHunt.')
@section('canonical', route('home'))

@section('content')
<div class="w-full h-full">
    <div class="p-6 max-w-none">

        {{-- Slide Top Descontos --}}
        <div class="mb-6 sm:mb-10 w-full px-2 sm:px-0">
            <div class="swiper mySwiper h-64 sm:h-80 md:h-96 lg:h-[28rem] rounded-xl sm:rounded-2xl w-full overflow-hidden">
                <div class="swiper-wrapper h-full rounded-xl sm:rounded-2xl">
                    @foreach($topGames as $game)
                    <div class="swiper-slide flex items-center justify-center h-full">
                        <div class="relative w-full h-full rounded-xl sm:rounded-2xl overflow-hidden shadow-2xl flex items-end">
                            <img src="{{ $game->optimized_image ?? $game->image }}" alt="{{ $game->title }}"
                                class="absolute inset-0 w-full h-full object-cover z-0"
                                loading="lazy"
                                decoding="async"
                                onerror="this.onerror=null; this.src='https://via.placeholder.com/1200x600/1a1a1a/ffffff?text={{ urlencode($game->title) }}';" />
                            {{-- Desconto geométrico no topo direito --}}
                            <div class="absolute top-2 sm:top-4 right-0 z-20">
                                <div class="discount-tag flex items-center justify-center px-3 sm:px-6 py-1 sm:py-2">
                                    <span class="text-white font-bold text-sm sm:text-lg drop-shadow">-{{ $game->discount_percent }}%</span>
                                </div>
                            </div>
                            {{-- Caixa de texto compacta e proporcional --}}
                            <div class="relative z-10 p-3 sm:p-4 md:p-5 lg:p-6 w-full flex flex-col items-start bg-black/70 dark:bg-black/80 rounded-b-xl sm:rounded-b-2xl">
                                <h2 class="text-sm sm:text-base md:text-lg lg:text-xl font-bold text-white drop-shadow mb-1 sm:mb-2 line-clamp-2">{{ $game->title }}</h2>
                                <div class="flex items-center gap-2 sm:gap-3 mb-2">
                                    <span class="text-base sm:text-lg md:text-xl font-bold text-green-400 drop-shadow">
                                        <x-price :price="$game->sale_price" />
                                    </span>
                                    @if($game->normal_price > $game->sale_price)
                                        <span class="text-xs sm:text-sm text-gray-300 line-through">
                                            <x-price :price="$game->normal_price" />
                                        </span>
                                    @endif
                                </div>
                                <a href="{{ route('games.show', $game) }}"
                                    class="bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800 text-white font-semibold px-3 sm:px-4 md:px-5 py-1.5 sm:py-2 rounded-md shadow transition text-xs sm:text-sm">Ver Oferta</a>
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
            <div class="bg-gray-800 dark:bg-gray-900 rounded-lg p-3 sm:p-4 mb-4 sm:mb-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 lg:gap-4">
                    <!-- Busca -->
                    <div class="sm:col-span-2 lg:col-span-1">
                        <label class="block text-xs sm:text-sm font-medium text-gray-300 dark:text-gray-400 mb-0.5 sm:mb-1">Buscar Jogo</label>
                        <input 
                            x-model="search" 
                            @input.debounce.500="updateFilters()" 
                            type="text"
                            placeholder="Nome do jogo..."
                            class="w-full bg-gray-700 dark:bg-gray-800 border border-gray-600 dark:border-gray-700 rounded-md px-2.5 sm:px-3 py-1.5 sm:py-2 text-sm sm:text-base text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent" />
                    </div>

                    <!-- Categoria -->
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-300 dark:text-gray-400 mb-0.5 sm:mb-1">Categoria</label>
                        <select 
                            x-model="category" 
                            @change="updateFilters()"
                            class="w-full bg-gray-700 dark:bg-gray-800 border border-gray-600 dark:border-gray-700 rounded-md px-2.5 sm:px-3 py-1.5 sm:py-2 text-sm sm:text-base text-white focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                            <option value="">Todas</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->name }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Loja -->
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-300 dark:text-gray-400 mb-0.5 sm:mb-1">Loja</label>
                        <select 
                            x-model="store" 
                            @change="updateFilters()"
                            class="w-full bg-gray-700 dark:bg-gray-800 border border-gray-600 dark:border-gray-700 rounded-md px-2.5 sm:px-3 py-1.5 sm:py-2 text-sm sm:text-base text-white focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                            <option value="">Todas</option>
                            @foreach($storeNames as $storeName)
                                <option value="{{ $storeName }}">{{ $storeName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Desconto Mínimo -->
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-300 dark:text-gray-400 mb-0.5 sm:mb-1">Desconto Mín.</label>
                        <input 
                            x-model="min_discount" 
                            @input.debounce.500="updateFilters()" 
                            type="number"
                            min="0" 
                            max="100" 
                            placeholder="%"
                            class="w-full bg-gray-700 dark:bg-gray-800 border border-gray-600 dark:border-gray-700 rounded-md px-2.5 sm:px-3 py-1.5 sm:py-2 text-sm sm:text-base text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent" />
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
    min-width: 50px;
    min-height: 28px;
    position: relative;
    clip-path: polygon(18% 0, 100% 0, 100% 100%, 18% 100%, 0 50%);
    box-shadow: 0 2px 8px rgba(0,0,0,0.18);
    font-family: inherit;
    font-weight: bold;
    letter-spacing: 0.5px;
}

@media (min-width: 640px) {
    .discount-tag {
        min-width: 70px;
        min-height: 38px;
        letter-spacing: 1px;
    }
}

.discount-tag::before {
    content: '';
    display: block;
    position: absolute;
    left: 6px;
    top: 50%;
    transform: translateY(-50%);
    width: 6px;
    height: 6px;
    background: #fff;
    border-radius: 50%;
}

@media (min-width: 640px) {
    .discount-tag::before {
        left: 8px;
        width: 8px;
        height: 8px;
    }
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

/* Ajustes responsivos para botões do Swiper */
.swiper-button-prev,
.swiper-button-next {
    width: 30px !important;
    height: 30px !important;
    margin-top: -15px !important;
}

@media (min-width: 640px) {
    .swiper-button-prev,
    .swiper-button-next {
        width: 44px !important;
        height: 44px !important;
        margin-top: -22px !important;
    }
}

.swiper-button-prev::after,
.swiper-button-next::after {
    font-size: 16px !important;
}

@media (min-width: 640px) {
    .swiper-button-prev::after,
    .swiper-button-next::after {
        font-size: 22px !important;
    }
}

/* Paginação do Swiper */
.swiper-pagination {
    position: relative !important;
    margin-top: 0.75rem !important;
    padding: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
    left: 0 !important;
    transform: none !important;
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
    gap: 0.5rem !important;
    flex-wrap: wrap !important;
}

.swiper-pagination-bullet {
    width: 8px !important;
    height: 8px !important;
    margin: 0 !important;
    opacity: 0.5 !important;
    flex-shrink: 0 !important;
}

.swiper-pagination-bullet-active {
    opacity: 1 !important;
    background: var(--color-primary, #dc2626) !important;
}

@media (min-width: 640px) {
    .swiper-pagination {
        margin-top: 1.5rem !important;
        gap: 0.75rem !important;
    }
    
    .swiper-pagination-bullet {
        width: 12px !important;
        height: 12px !important;
    }
}
</style>

    </div>
</div>
@endsection