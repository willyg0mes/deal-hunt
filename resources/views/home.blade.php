@extends('layouts.app')

@section('title', 'DealHunt — ofertas de jogos em tempo real')
@section('meta_description', 'Encontre as melhores ofertas de games na Steam, Epic e outras lojas. Wishlist, alertas e descontos atualizados no DealHunt.')
@section('canonical', route('home'))

@section('content')
<div class="w-full">
    <div class="px-4 sm:px-6 py-6 max-w-[1600px] mx-auto">

        {{-- Carrossel de destaques: vários cards, o do meio em evidência --}}
        @if($topGames->isNotEmpty())
        <section class="mb-8 sm:mb-10">
            <div class="flex items-end justify-between mb-4 px-0.5">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Em destaque</h1>
                    <p class="text-xs sm:text-sm text-gray-400">Maiores descontos — o do centro é o destaque do momento</p>
                </div>
                <div class="hidden sm:flex items-center gap-2 text-xs text-gray-500">
                    <span class="inline-block w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                    Auto
                </div>
            </div>

            <div class="featured-swiper-wrap relative">
                <div class="swiper featuredSwiper">
                    <div class="swiper-wrapper items-center py-4 sm:py-6">
                        @foreach($topGames as $game)
                        <div class="swiper-slide featured-slide">
                            <a href="{{ route('games.show', $game) }}"
                               class="featured-card group relative block overflow-hidden rounded-xl border border-gray-700/60 bg-gray-950 shadow-lg shadow-black/40 transition-shadow duration-300">
                                <div class="game-cover relative w-full overflow-hidden">
                                    <img src="{{ $game->optimized_image }}" alt="{{ $game->title }}"
                                         class="absolute inset-0 h-full w-full object-cover object-center transition duration-500 group-hover:scale-[1.03]"
                                         loading="lazy"
                                         decoding="async"
                                         width="460"
                                         height="215"
                                         onerror="this.onerror=null; this.src='{{ $game->placeholderCoverUrl() }}';" />
                                    <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/85 via-black/25 to-transparent"></div>

                                    <span class="absolute top-2 left-2 rounded bg-emerald-600 px-2 py-0.5 text-[11px] font-bold text-white shadow">
                                        -{{ $game->discount_percent }}%
                                    </span>
                                    <span class="absolute top-2 right-2 rounded px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white shadow {{ $game->store_color }}">
                                        {{ $game->store_name }}
                                    </span>
                                </div>

                                <div class="featured-card-body relative px-3 py-2.5 sm:px-3.5 sm:py-3">
                                    <h2 class="text-sm sm:text-base font-bold text-white leading-snug line-clamp-2 min-h-[2.5rem]">{{ $game->title }}</h2>
                                    <div class="mt-1.5 flex items-baseline gap-2">
                                        <span class="text-base sm:text-lg font-bold text-emerald-400"><x-price :price="$game->sale_price" /></span>
                                        @if($game->normal_price > $game->sale_price)
                                            <span class="text-xs text-gray-500 line-through"><x-price :price="$game->normal_price" /></span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>

                <button type="button" class="featured-nav featured-prev" aria-label="Anterior">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button type="button" class="featured-nav featured-next" aria-label="Próximo">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>

                <div class="featured-pagination mt-3 flex justify-center"></div>
            </div>
        </section>
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

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const el = document.querySelector('.featuredSwiper');
        if (!el || typeof Swiper === 'undefined') return;

        new Swiper('.featuredSwiper', {
            effect: 'coverflow',
            grabCursor: true,
            centeredSlides: true,
            loop: {{ $topGames->count() >= 5 ? 'true' : 'false' }},
            slidesPerView: 1.25,
            spaceBetween: 12,
            coverflowEffect: {
                rotate: 0,
                stretch: -10,
                depth: 120,
                modifier: 1.4,
                slideShadows: false,
            },
            autoplay: {
                delay: 3500,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
            navigation: {
                nextEl: '.featured-next',
                prevEl: '.featured-prev',
            },
            pagination: {
                el: '.featured-pagination',
                clickable: true,
            },
            breakpoints: {
                640: { slidesPerView: 2.2, spaceBetween: 16 },
                900: { slidesPerView: 3, spaceBetween: 18 },
                1200: { slidesPerView: 3.4, spaceBetween: 20 },
            },
        });
    });
</script>

<style>
.featured-swiper-wrap {
    --featured-pad: 0.5rem;
}

.featuredSwiper {
    overflow: visible;
    padding: 0 var(--featured-pad);
}

.featured-slide {
    height: auto;
    transition: opacity 0.35s ease;
}

.featured-card {
    transform: scale(0.88);
    opacity: 0.55;
    transition: transform 0.4s ease, opacity 0.35s ease, box-shadow 0.35s ease;
    will-change: transform, opacity;
}

.swiper-slide-active .featured-card,
.swiper-slide-duplicate-active .featured-card {
    transform: scale(1.06);
    opacity: 1;
    box-shadow: 0 18px 40px rgba(0, 0, 0, 0.55), 0 0 0 1px rgba(185, 28, 28, 0.35);
    z-index: 2;
}

.swiper-slide-prev .featured-card,
.swiper-slide-next .featured-card,
.swiper-slide-duplicate-prev .featured-card,
.swiper-slide-duplicate-next .featured-card {
    transform: scale(0.94);
    opacity: 0.78;
}

.featured-nav {
    position: absolute;
    top: 42%;
    z-index: 20;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 9999px;
    border: 1px solid rgba(75, 85, 99, 0.8);
    background: rgba(17, 24, 39, 0.85);
    color: #f3f4f6;
    backdrop-filter: blur(6px);
    transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease;
}
.featured-nav:hover {
    background: rgba(185, 28, 28, 0.9);
    border-color: rgba(248, 113, 113, 0.6);
    color: #fff;
}
.featured-prev { left: 0; }
.featured-next { right: 0; }

@media (max-width: 639px) {
    .featured-nav { display: none; }
    .featured-card { transform: scale(0.94); opacity: 0.7; }
    .swiper-slide-active .featured-card {
        transform: scale(1.02);
        opacity: 1;
    }
}

.featured-pagination .swiper-pagination-bullet {
    background: #6b7280;
    opacity: 0.55;
}
.featured-pagination .swiper-pagination-bullet-active {
    background: var(--color-primary);
    opacity: 1;
}

[x-cloak] { display: none !important; }
</style>
@endsection
