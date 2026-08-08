@extends('layouts.app')

@section('title', 'DealHunt — ofertas de jogos em tempo real')
@section('meta_description', 'Encontre as melhores ofertas de games na Steam, Epic e outras lojas. Wishlist, alertas e descontos atualizados no DealHunt.')
@section('canonical', route('home'))

@section('content')
<div class="w-full">
    <div class="px-4 sm:px-6 py-6 max-w-[1600px] mx-auto">

        {{-- Destaques --}}
        @if($topGames->isNotEmpty())
        <div class="mb-8 sm:mb-10">
            <div class="flex items-end justify-between mb-3 px-0.5">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Em destaque</h1>
                    <p class="text-xs sm:text-sm text-gray-400">Maiores descontos agora</p>
                </div>
            </div>
            <div class="swiper mySwiper h-56 sm:h-72 md:h-80 lg:h-[22rem] rounded-2xl overflow-hidden border border-gray-700/40 shadow-2xl shadow-black/40">
                <div class="swiper-wrapper">
                    @foreach($topGames as $game)
                    <div class="swiper-slide">
                        <a href="{{ route('games.show', $game) }}" class="relative block w-full h-full overflow-hidden group">
                            <img src="{{ $game->optimized_image }}" alt="{{ $game->title }}"
                                class="absolute inset-0 w-full h-full object-cover object-center transition duration-700 group-hover:scale-105"
                                loading="lazy"
                                decoding="async"
                                onerror="this.onerror=null; this.src='{{ $game->placeholderCoverUrl() }}';" />
                            <div class="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent"></div>
                            <div class="absolute top-3 right-0 z-20">
                                <div class="discount-tag flex items-center justify-center px-4 sm:px-6 py-1.5 sm:py-2">
                                    <span class="text-white font-bold text-sm sm:text-lg">-{{ $game->discount_percent }}%</span>
                                </div>
                            </div>
                            <div class="absolute bottom-0 left-0 right-0 z-10 p-4 sm:p-6">
                                <h2 class="text-base sm:text-xl lg:text-2xl font-bold text-white mb-2 line-clamp-2">{{ $game->title }}</h2>
                                <div class="flex items-center gap-3 mb-3">
                                    <span class="text-lg sm:text-2xl font-bold text-emerald-400"><x-price :price="$game->sale_price" /></span>
                                    @if($game->normal_price > $game->sale_price)
                                        <span class="text-sm text-gray-400 line-through"><x-price :price="$game->normal_price" /></span>
                                    @endif
                                    <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded {{ $game->store_color }} text-white">{{ $game->store_name }}</span>
                                </div>
                                <span class="inline-block bg-red-600 group-hover:bg-red-700 text-white font-semibold px-4 py-2 rounded-lg text-sm transition">Ver oferta</span>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
        @endif

        @include('partials.games-filters', [
            'ajax' => true,
            'stores' => $storeNames,
            'categories' => $categories,
            'showFree' => true,
            'showDiscount' => true,
            'showPrice' => true,
        ])

        <div id="games-list" class="w-full">
            @include('partials.games-list', ['games' => $games])
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css" />
<script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (!document.querySelector('.mySwiper')) return;
        new Swiper('.mySwiper', {
            slidesPerView: 1,
            spaceBetween: 0,
            loop: true,
            autoplay: { delay: 4000, disableOnInteraction: false },
            navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
            pagination: { el: '.swiper-pagination', clickable: true },
        });
    });
</script>

<style>
.discount-tag {
    background: var(--color-primary);
    color: #fff;
    min-width: 56px;
    min-height: 30px;
    position: relative;
    clip-path: polygon(18% 0, 100% 0, 100% 100%, 18% 100%, 0 50%);
    box-shadow: 0 2px 8px rgba(0,0,0,0.25);
    font-weight: bold;
}
@media (min-width: 640px) {
    .discount-tag { min-width: 72px; min-height: 38px; }
}
.discount-tag::before {
    content: '';
    position: absolute;
    left: 6px;
    top: 50%;
    transform: translateY(-50%);
    width: 6px;
    height: 6px;
    background: #fff;
    border-radius: 50%;
}
.swiper-button-next, .swiper-button-prev { color: #fff !important; }
.swiper-pagination-bullet-active { background: var(--color-primary) !important; }
[x-cloak] { display: none !important; }
</style>
@endsection
