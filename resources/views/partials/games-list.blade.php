@if($games->total() > 0)
    <div class="mb-4 text-sm text-gray-400 flex items-center justify-between gap-2">
        <span>
            <span class="text-white font-semibold">{{ $games->firstItem() }}–{{ $games->lastItem() }}</span>
            de
            <span class="text-white font-semibold">{{ $games->total() }}</span>
            jogos
        </span>
        <span class="hidden sm:inline text-xs text-gray-500">
            Página {{ $games->currentPage() }} / {{ $games->lastPage() }}
        </span>
    </div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4 sm:gap-5">
    @forelse($games as $game)
    <article class="group flex flex-col overflow-hidden rounded-xl border border-gray-700/50 bg-gray-900/80 shadow-lg shadow-black/40 transition duration-300 hover:border-red-800/40 hover:shadow-red-950/30 hover:-translate-y-0.5">
        {{-- Capa no aspect Steam header (460×215) — object-cover sem altura arbitrária --}}
        <a href="{{ route('games.show', $game) }}" class="relative block w-full aspect-[460/215] overflow-hidden bg-gray-950">
            <img
                src="{{ $game->optimized_image }}"
                alt="{{ $game->title }}"
                class="absolute inset-0 h-full w-full object-cover object-center transition duration-500 group-hover:scale-[1.04]"
                loading="lazy"
                decoding="async"
                width="460"
                height="215"
                onerror="this.onerror=null; this.src='{{ $game->placeholderCoverUrl() }}';"
            />
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-80"></div>

            @if($game->is_free_permanently ?? false)
                <span class="absolute bottom-2 left-2 rounded bg-emerald-600 px-2.5 py-1 text-xs font-bold text-white shadow">GRÁTIS</span>
            @elseif($game->discount_percent > 0)
                <span class="absolute bottom-2 left-2 rounded bg-emerald-600 px-2.5 py-1 text-xs font-bold text-white shadow">-{{ $game->discount_percent }}%</span>
            @endif

            <span class="absolute top-2 right-2 rounded px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white shadow {{ $game->store_color }}">
                {{ $game->store_name }}
            </span>
        </a>

        <div class="flex flex-1 flex-col gap-2 p-3.5 sm:p-4">
            <h3 class="text-sm sm:text-base font-bold text-white leading-snug line-clamp-2 min-h-[2.5rem]">
                <a href="{{ route('games.show', $game) }}" class="hover:text-red-300 transition-colors">{{ $game->title }}</a>
            </h3>

            @if($game->categories->count() > 0)
            <div class="flex flex-wrap gap-1">
                @foreach($game->categories->take(2) as $category)
                    <span class="text-[10px] uppercase tracking-wide bg-gray-800 text-gray-300 px-1.5 py-0.5 rounded">{{ $category->name }}</span>
                @endforeach
                @if($game->categories->count() > 2)
                    <span class="text-[10px] text-gray-500">+{{ $game->categories->count() - 2 }}</span>
                @endif
            </div>
            @endif

            <div class="mt-auto pt-2 border-t border-gray-800/80">
                <div class="flex items-baseline gap-2 mb-2">
                    @if($game->is_free_permanently ?? false)
                        <span class="text-lg font-bold text-emerald-400">Grátis</span>
                    @else
                        <span class="text-lg font-bold text-emerald-400"><x-price :price="$game->sale_price" /></span>
                        @if($game->normal_price > $game->sale_price)
                            <span class="text-xs text-gray-500 line-through"><x-price :price="$game->normal_price" /></span>
                        @endif
                    @endif
                </div>

                @if(!($game->is_free_permanently ?? false) && $game->deal_ends_at)
                <div class="text-[11px] text-amber-400/90 mb-2">
                    @php
                        $dealEndsAt = $game->deal_ends_at instanceof \Carbon\Carbon
                            ? $game->deal_ends_at
                            : \Carbon\Carbon::parse($game->deal_ends_at);
                    @endphp
                    Até {{ $dealEndsAt->format('d/m H:i') }}
                    @if($dealEndsAt->isToday())
                        <span class="text-red-400 font-semibold">(hoje)</span>
                    @elseif($dealEndsAt->isTomorrow())
                        <span class="text-amber-300 font-semibold">(amanhã)</span>
                    @elseif($dealEndsAt->isFuture())
                        @php $daysLeft = max(1, (int) ceil(now()->diffInDays($dealEndsAt))); @endphp
                        @if($daysLeft <= 7)
                            <span class="text-amber-300 font-semibold">({{ $daysLeft }}d)</span>
                        @endif
                    @endif
                </div>
                @endif

                <div class="flex gap-2">
                    <a href="{{ route('games.show', $game) }}"
                        class="flex-1 rounded-lg bg-red-600 hover:bg-red-700 text-white font-semibold px-3 py-2 text-center text-sm transition">
                        {{ ($game->is_free_permanently ?? false) ? 'Detalhes' : 'Ver oferta' }}
                    </a>

                    @auth
                        @php $isInWishlist = auth()->user()->wishlist()->where('game_id', $game->id)->exists(); @endphp
                        <form method="POST" action="{{ route('wishlist.toggle', $game) }}" class="wishlist-form" data-game-id="{{ $game->id }}">
                            @csrf
                            <button type="submit"
                                class="rounded-lg px-3 py-2 transition wishlist-btn {{ $isInWishlist ? 'bg-amber-500 hover:bg-amber-600 text-white' : 'bg-gray-700 hover:bg-gray-600 text-gray-200' }}"
                                title="{{ $isInWishlist ? 'Remover da wishlist' : 'Adicionar à wishlist' }}"
                                data-in-wishlist="{{ $isInWishlist ? 'true' : 'false' }}">
                                <span class="wishlist-icon">
                                    @if($isInWishlist)
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path></svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                                    @endif
                                </span>
                            </button>
                        </form>
                    @else
                        <button type="button" onclick="showLoginPrompt()"
                            class="rounded-lg px-3 py-2 bg-gray-700 hover:bg-gray-600 text-gray-200 transition"
                            title="Faça login para adicionar à wishlist">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                        </button>
                    @endauth
                </div>
            </div>
        </div>
    </article>
    @empty
    <div class="col-span-full text-center py-16 text-gray-400">
        <p class="text-lg font-medium text-gray-300 mb-1">Nenhum jogo encontrado</p>
        <p class="text-sm">Ajuste os filtros ou limpe a busca.</p>
    </div>
    @endforelse
</div>

@if($games->hasPages())
<div class="mt-8 flex justify-center">
    {{ $games->appends(request()->query())->links('vendor.pagination.custom') }}
</div>
@endif
