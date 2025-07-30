@if($games->total() > 0)
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-300 flex items-center justify-between">
        <span>
            Exibindo
            <span class="font-bold text-red-600 dark:text-red-400">{{ $games->firstItem() }}</span>
            a
            <span class="font-bold text-red-600 dark:text-red-400">{{ $games->lastItem() }}</span>
            de
            <span class="font-bold text-red-600 dark:text-red-400">{{ $games->total() }}</span>
            jogos encontrados
        </span>
        <span class="hidden sm:inline text-xs text-gray-500 dark:text-gray-400">
            Página {{ $games->currentPage() }} de {{ $games->lastPage() }}
        </span>
    </div>
@endif
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
    @foreach($games as $game)
    <div class="bg-gradient-to-br from-gray-800 via-red-900/30 to-red-800/30 dark:from-gray-800 dark:via-gray-700 dark:to-gray-900 rounded-md shadow-lg shadow-black/50 dark:shadow-black/80 overflow-hidden flex flex-col h-[450px] border border-gray-700/50 dark:border-gray-600/50 transition-all duration-300 hover:shadow-xl hover:shadow-red-900/20 dark:hover:shadow-gray-900/40">
        <!-- Imagem com altura fixa -->
        <div class="relative h-[200px] w-full overflow-hidden">
            <img src="{{ $game->image }}" alt="{{ $game->title }}" class="w-full h-full object-cover" />
            @if($game->discount_percent > 0)
            <span class="absolute bottom-2 left-2 bg-green-600 dark:bg-green-700 text-white text-sm font-bold px-3 py-1 rounded shadow">
                Até -{{ $game->discount_percent }}%
            </span>
            @endif
            <!-- Badge da loja no topo direito -->
            <div class="absolute top-2 right-2">
                <span class="text-xs font-bold px-2 py-1 rounded {{ $game->store_color }} text-white shadow">
                    {{ $game->store_name }}
                </span>
            </div>
        </div>
        
        <!-- Conteúdo com altura flexível -->
        <div class="flex-1 flex flex-col p-5">
            <!-- Título com altura fixa -->
            <h3 class="text-lg font-bold text-white dark:text-white mb-2 h-[50px] flex items-start overflow-hidden">
                <span class="line-clamp-2 leading-tight">{{ $game->title }}</span>
            </h3>
            
            <!-- Categorias -->
            @if($game->categories->count() > 0)
            <div class="flex flex-wrap gap-1 mb-3">
                @foreach($game->categories->take(3) as $category)
                    <span class="text-xs bg-gray-700/80 dark:bg-gray-700/60 text-gray-200 dark:text-gray-300 px-2 py-0.5 rounded backdrop-blur-sm">
                        {{ $category->name }}
                    </span>
                @endforeach
                @if($game->categories->count() > 3)
                    <span class="text-xs bg-gray-600/80 dark:bg-gray-600/60 text-gray-300 dark:text-gray-400 px-2 py-0.5 rounded backdrop-blur-sm">
                        +{{ $game->categories->count() - 3 }}
                    </span>
                @endif
            </div>
            @endif
            
            <!-- Info da loja -->
            <p class="text-sm text-gray-200 dark:text-gray-300 mb-3">Disponível na {{ $game->store_name }}</p>
            
            <!-- Descrição com altura flexível -->
            <div class="flex-1 mb-4">
                <p class="text-sm text-gray-300 dark:text-gray-400 line-clamp-3">{{ $game->description ?? 'Oferta especial disponível!' }}</p>
            </div>
            
            <!-- Preços e botão sempre no final -->
            <div class="mt-auto">
                <div class="flex items-center gap-3 mb-2">
                    <span class="text-lg font-bold text-green-600 dark:text-green-400">
                        <x-price :price="$game->sale_price" />
                    </span>
                    @if($game->normal_price > $game->sale_price)
                        <span class="text-sm text-gray-400 dark:text-gray-500 line-through">
                            <x-price :price="$game->normal_price" />
                        </span>
                    @endif
                </div>
                @if($game->deal_ends_at)
                <div class="text-xs text-orange-500 dark:text-orange-400 mb-3">
                    @php
                        $dealEndsAt = $game->deal_ends_at instanceof \Carbon\Carbon 
                            ? $game->deal_ends_at 
                            : \Carbon\Carbon::parse($game->deal_ends_at);
                    @endphp
                    ⏰ Expira em: {{ $dealEndsAt->format('d/m/Y H:i') }}
                    @if($dealEndsAt->isToday())
                        <span class="text-red-500 dark:text-red-400 font-bold">(Hoje!)</span>
                    @elseif($dealEndsAt->isTomorrow())
                        <span class="text-yellow-500 dark:text-yellow-400 font-bold">(Amanhã)</span>
                    @elseif($dealEndsAt->isFuture())
                        @php
                            $daysLeft = max(1, ceil(now()->diffInDays($dealEndsAt)));
                        @endphp
                        @if($daysLeft <= 7)
                            <span class="text-yellow-500 dark:text-yellow-400 font-bold">({{ $daysLeft }} {{ $daysLeft == 1 ? 'dia' : 'dias' }})</span>
                        @endif
                    @elseif($dealEndsAt->isPast())
                        <span class="text-red-500 dark:text-red-400 font-bold">(Expirada)</span>
                    @endif
                </div>
                @endif
                
                <!-- Botões de ação -->
                <div class="flex gap-2">
                    <a href="{{ route('games.show', $game) }}"
                        class="flex-1 bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800 text-white font-semibold px-4 py-2 rounded shadow transition text-center">
                        Ver Oferta
                    </a>
                    
                    @auth
                        <!-- Botão de Wishlist para usuários logados -->
                        @php
                            $isInWishlist = auth()->user()->wishlist()->where('game_id', $game->id)->exists();
                        @endphp
                        <form method="POST" action="{{ route('wishlist.toggle', $game) }}" class="inline-block wishlist-form" data-game-id="{{ $game->id }}">
                            @csrf
                            <button type="submit" 
                                class="px-3 py-2 rounded shadow transition wishlist-btn {{ $isInWishlist ? 'bg-yellow-500 hover:bg-yellow-600 text-white' : 'bg-gray-600 hover:bg-gray-500 text-gray-200' }}"
                                title="{{ $isInWishlist ? 'Remover da Wishlist' : 'Adicionar à Wishlist' }}"
                                data-in-wishlist="{{ $isInWishlist ? 'true' : 'false' }}">>
                                <span class="wishlist-icon">
                                    @if($isInWishlist)
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                        </svg>
                                    @endif
                                </span>
                            </button>
                        </form>
                    @else
                        <!-- Botão de Wishlist para usuários não logados -->
                        <button onclick="showLoginPrompt()" 
                            class="px-3 py-2 bg-gray-600 hover:bg-gray-500 text-gray-200 rounded shadow transition"
                            title="Faça login para adicionar à wishlist">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </button>
                    @endauth
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
<div class="mt-6 flex justify-center">
    {{ $games->links('vendor.pagination.custom') }}
</div>