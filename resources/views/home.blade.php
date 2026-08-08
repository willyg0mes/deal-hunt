@extends('layouts.app')

@section('title', 'DealHunt — ofertas de jogos em tempo real')
@section('meta_description', 'Encontre as melhores ofertas de games na Steam, Epic e outras lojas. Wishlist, alertas e descontos atualizados no DealHunt.')
@section('canonical', route('home'))

@section('content')
<div class="w-full">
    <div class="px-4 sm:px-6 py-6 max-w-[1600px] mx-auto">

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

            <div class="featured-swiper-wrap relative px-0 sm:px-8">
                <div class="swiper featuredSwiper">
                    <div class="swiper-wrapper">
                        @foreach($topGames as $index => $game)
                        <div class="swiper-slide featured-slide">
                            <a href="{{ route('games.show', $game) }}" class="featured-card group">
                                <div class="featured-media">
                                    <img
                                        src="{{ $game->optimized_image }}"
                                        alt="{{ $game->title }}"
                                        width="460"
                                        height="215"
                                        loading="{{ $index < 4 ? 'eager' : 'lazy' }}"
                                        decoding="async"
                                        onerror="this.onerror=null; this.src='{{ $game->placeholderCoverUrl() }}';"
                                    />
                                    <div class="featured-media-fade"></div>
                                    <span class="featured-badge-discount">-{{ $game->discount_percent }}%</span>
                                    <span class="featured-badge-store {{ $game->store_color }}">{{ $game->store_name }}</span>
                                </div>
                                <div class="featured-card-body">
                                    <h2>{{ $game->title }}</h2>
                                    <div class="featured-prices">
                                        <span class="featured-price"><x-price :price="$game->sale_price" /></span>
                                        @if($game->normal_price > $game->sale_price)
                                            <span class="featured-price-old"><x-price :price="$game->normal_price" /></span>
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

                <div class="featured-pagination"></div>
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
            slidesPerView: 1.15,
            centeredSlides: true,
            spaceBetween: 14,
            loop: {{ $topGames->count() >= 5 ? 'true' : 'false' }},
            speed: 900,
            grabCursor: true,
            watchSlidesProgress: true,
            autoplay: {
                delay: 4200,
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
                640:  { slidesPerView: 2.1, spaceBetween: 18 },
                900:  { slidesPerView: 2.6, spaceBetween: 20 },
                1200: { slidesPerView: 3.15, spaceBetween: 22 },
            },
        });
    });
</script>

<style>
.featured-swiper-wrap {
    overflow: hidden;
    padding-bottom: 0.25rem;
}

.featuredSwiper {
    overflow: visible;
    padding: 1.25rem 0 1.5rem;
}

.featured-slide {
    height: auto;
    transition: transform 0.9s cubic-bezier(0.22, 1, 0.36, 1),
                opacity 0.9s cubic-bezier(0.22, 1, 0.36, 1);
    opacity: 0.55;
    transform: scale(0.9);
}

.swiper-slide-prev,
.swiper-slide-next,
.swiper-slide-duplicate-prev,
.swiper-slide-duplicate-next {
    opacity: 0.78;
    transform: scale(0.95);
}

.swiper-slide-active,
.swiper-slide-duplicate-active {
    opacity: 1;
    transform: scale(1.05);
    z-index: 3;
}

.featured-card {
    display: block;
    overflow: hidden;
    border-radius: 0.9rem;
    border: 1px solid rgba(75, 85, 99, 0.55);
    background: #0b0f17;
    box-shadow: 0 10px 28px rgba(0, 0, 0, 0.35);
    transition: box-shadow 0.9s cubic-bezier(0.22, 1, 0.36, 1),
                border-color 0.9s ease;
    text-decoration: none;
}

.swiper-slide-active .featured-card,
.swiper-slide-duplicate-active .featured-card {
    border-color: rgba(185, 28, 28, 0.45);
    box-shadow: 0 22px 48px rgba(0, 0, 0, 0.55), 0 0 0 1px rgba(185, 28, 28, 0.25);
}

/* Capa com proporção estável (não depende de absolute + aspect em transform 3D) */
.featured-media {
    position: relative;
    width: 100%;
    aspect-ratio: 460 / 215;
    background: #111827;
    overflow: hidden;
}

.featured-media img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    transition: transform 1.1s cubic-bezier(0.22, 1, 0.36, 1);
}

.featured-card:hover .featured-media img {
    transform: scale(1.04);
}

.featured-media-fade {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.55) 0%, transparent 55%);
    pointer-events: none;
}

.featured-badge-discount {
    position: absolute;
    top: 0.5rem;
    left: 0.5rem;
    background: #059669;
    color: #fff;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.2rem 0.5rem;
    border-radius: 0.35rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.35);
}

.featured-badge-store {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    color: #fff;
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    padding: 0.2rem 0.45rem;
    border-radius: 0.35rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.35);
}

.featured-card-body {
    padding: 0.75rem 0.9rem 0.9rem;
}

.featured-card-body h2 {
    margin: 0;
    color: #fff;
    font-size: 0.95rem;
    font-weight: 700;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 2.5rem;
}

.featured-prices {
    margin-top: 0.4rem;
    display: flex;
    align-items: baseline;
    gap: 0.5rem;
}

.featured-price {
    color: #34d399;
    font-size: 1.05rem;
    font-weight: 700;
}

.featured-price-old {
    color: #6b7280;
    font-size: 0.75rem;
    text-decoration: line-through;
}

.featured-nav {
    position: absolute;
    top: 42%;
    z-index: 20;
    display: none;
    align-items: center;
    justify-content: center;
    width: 2.6rem;
    height: 2.6rem;
    border-radius: 9999px;
    border: 1px solid rgba(75, 85, 99, 0.8);
    background: rgba(17, 24, 39, 0.88);
    color: #f3f4f6;
    backdrop-filter: blur(6px);
    transition: background 0.25s ease, border-color 0.25s ease, transform 0.25s ease;
}
.featured-nav:hover {
    background: rgba(185, 28, 28, 0.92);
    border-color: rgba(248, 113, 113, 0.55);
    transform: scale(1.06);
}
.featured-prev { left: 0; }
.featured-next { right: 0; }

@media (min-width: 640px) {
    .featured-nav { display: flex; }
}

.featured-pagination {
    display: flex;
    justify-content: center;
    gap: 0.35rem;
    margin-top: 0.25rem;
}
.featured-pagination .swiper-pagination-bullet {
    width: 0.5rem;
    height: 0.5rem;
    background: #6b7280;
    opacity: 0.45;
    transition: width 0.45s cubic-bezier(0.22, 1, 0.36, 1),
                opacity 0.45s ease,
                background 0.45s ease,
                border-radius 0.45s ease;
}
.featured-pagination .swiper-pagination-bullet-active {
    width: 1.35rem;
    border-radius: 9999px;
    background: var(--color-primary);
    opacity: 1;
}

[x-cloak] { display: none !important; }
</style>
@endsection
