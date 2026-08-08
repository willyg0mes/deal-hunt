@extends('layouts.app')

@section('title', $game->title.($game->discount_percent ? ' — '.$game->discount_percent.'% off' : '').' | '.config('app.name', 'DealHunt'))
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($game->getBestDescription($rawgData) ?: ($game->title.' com desconto na '.($game->store_name ?: 'loja digital').'. Confira no DealHunt.')), 160))
@section('canonical', route('games.show', $game))
@section('og_type', 'product')
@section('og_image', $game->optimized_image ?: (($rawgData['background_image'] ?? null) ?: asset('favicon.ico')))

@section('content')
@php
// Função para traduzir classificações ESRB
function translateESRBRating($rating) {
    $translations = [
        'Everyone' => 'Livre',
        'Everyone 10+' => 'Livre +10',
        'Teen' => 'Adolescente',
        'Mature 17+' => 'Adulto +17',
        'Adults Only 18+' => 'Somente Adultos +18',
        'Rating Pending' => 'Classificação Pendente'
    ];
    
    return $translations[$rating] ?? $rating;
}

// Função para traduzir nomes de plataformas
function translatePlatform($platform) {
    $translations = [
        'PC' => 'PC',
        'PlayStation' => 'PlayStation',
        'PlayStation 2' => 'PlayStation 2',
        'PlayStation 3' => 'PlayStation 3', 
        'PlayStation 4' => 'PlayStation 4',
        'PlayStation 5' => 'PlayStation 5',
        'Xbox' => 'Xbox',
        'Xbox 360' => 'Xbox 360',
        'Xbox One' => 'Xbox One',
        'Xbox Series S/X' => 'Xbox Series S/X',
        'Nintendo Switch' => 'Nintendo Switch',
        'Nintendo DS' => 'Nintendo DS',
        'Nintendo 3DS' => 'Nintendo 3DS',
        'iOS' => 'iOS',
        'Android' => 'Android',
        'macOS' => 'macOS',
        'Linux' => 'Linux',
        'Web' => 'Navegador'
    ];
    
    return $translations[$platform] ?? $platform;
}

// Função para traduzir gêneros comuns
function translateGenre($genre) {
    $translations = [
        'Action' => 'Ação',
        'Adventure' => 'Aventura',
        'RPG' => 'RPG',
        'Strategy' => 'Estratégia',
        'Simulation' => 'Simulação',
        'Sports' => 'Esportes',
        'Racing' => 'Corrida',
        'Puzzle' => 'Quebra-cabeça',
        'Platformer' => 'Plataforma',
        'Fighting' => 'Luta',
        'Shooter' => 'Tiro',
        'Horror' => 'Terror',
        'Indie' => 'Independente',
        'Casual' => 'Casual',
        'Educational' => 'Educativo',
        'Massively Multiplayer' => 'Multijogador Online',
        'Family' => 'Família'
    ];
    
    return $translations[$genre] ?? $genre;
}

// Função para melhorar a apresentação de descrições em inglês
function formatDescription($description) {
    if (empty($description)) {
        return '';
    }
    
    // Remover tags HTML e limpar o texto
    $cleanText = strip_tags($description);
    
    // Traduzir palavras-chave comuns do gaming
    $translations = [
        // Termos de jogos
        'players' => 'jogadores',
        'player' => 'jogador', 
        'game' => 'jogo',
        'games' => 'jogos',
        'adventure' => 'aventura',
        'action' => 'ação',
        'RPG' => 'RPG',
        'strategy' => 'estratégia',
        'simulation' => 'simulação',
        'multiplayer' => 'multijogador',
        'single-player' => 'um jogador',
        'singleplayer' => 'um jogador',
        'online' => 'online',
        'offline' => 'offline',
        'story' => 'história',
        'characters' => 'personagens',
        'character' => 'personagem',
        'world' => 'mundo',
        'worlds' => 'mundos',
        'levels' => 'níveis',
        'level' => 'nível',
        'mission' => 'missão',
        'missions' => 'missões',
        'weapons' => 'armas',
        'weapon' => 'arma',
        'skills' => 'habilidades',
        'skill' => 'habilidade',
        'combat' => 'combate',
        'battle' => 'batalha',
        'battles' => 'batalhas',
        
        // Termos gerais
        'experience' => 'experiência',
        'journey' => 'jornada',
        'explore' => 'explorar',
        'discover' => 'descobrir',
        'create' => 'criar',
        'build' => 'construir',
        'survive' => 'sobreviver',
        'fight' => 'lutar',
        'win' => 'vencer',
        'challenge' => 'desafio',
        'challenges' => 'desafios',
        'quest' => 'missão',
        'quests' => 'missões',
        'fantasy' => 'fantasia',
        'epic' => 'épico',
        'legendary' => 'lendário',
        'ultimate' => 'supremo',
        'powerful' => 'poderoso',
        'dangerous' => 'perigoso',
        'mysterious' => 'misterioso',
        'ancient' => 'antigo',
        'modern' => 'moderno',
        'futuristic' => 'futurista',
        
        // Frases comuns
        'you are' => 'você é',
        'you will' => 'você vai',
        'you can' => 'você pode',
        'you must' => 'você deve',
        'take control' => 'assuma o controle',
        'join the' => 'junte-se ao',
        'become the' => 'torne-se o',
        'save the world' => 'salve o mundo',
        'save the' => 'salve o',
        'the best' => 'o melhor',
        'the most' => 'o mais',
        'of all time' => 'de todos os tempos',
        'and more' => 'e muito mais'
    ];
    
    foreach ($translations as $english => $portuguese) {
        $cleanText = preg_replace('/\b' . preg_quote($english, '/') . '\b/i', $portuguese, $cleanText);
    }
    
    return $cleanText;
}

// Função para detectar se o texto está em inglês
function isEnglishText($text) {
    // Lista de palavras muito comuns em inglês que raramente aparecem em português
    $englishWords = ['the', 'and', 'you', 'are', 'for', 'with', 'this', 'that', 'will', 'can', 'your', 'from', 'they', 'have', 'been', 'were', 'was'];
    $text = strtolower($text);
    $englishCount = 0;
    
    foreach ($englishWords as $word) {
        if (strpos($text, ' ' . $word . ' ') !== false || strpos($text, $word . ' ') === 0) {
            $englishCount++;
        }
    }
    
    // Se encontrar 3 ou mais palavras em inglês, considera como texto inglês
    return $englishCount >= 3;
}
@endphp

<div class="w-full h-full">
    <div class="p-6 max-w-none">
        
        <!-- Header Principal -->
        <div class="bg-gradient-to-br from-gray-800 via-red-900/30 to-red-800/30 dark:from-gray-800 dark:via-gray-700 dark:to-gray-900 rounded-2xl shadow-lg p-6 sm:p-8 mb-8">
            <div class="flex flex-col xl:flex-row gap-8">
                
                <!-- Capa (aspect Steam header 460×215) -->
                <div class="flex justify-center xl:justify-start xl:w-[min(100%,28rem)] shrink-0">
                    <div class="relative w-full max-w-md xl:max-w-none game-cover overflow-hidden rounded-xl shadow-lg border border-gray-600/50 bg-gray-950">
                        <img src="{{ $game->optimized_image }}" alt="{{ $game->title }}"
                             class="absolute inset-0 w-full h-full object-cover object-center"
                             width="460" height="215"
                             onerror="this.onerror=null; this.src='{{ $game->placeholderCoverUrl() }}';" />
                        @if($game->discount_percent > 0)
                            <div class="absolute top-3 right-3 bg-red-600 text-white text-sm font-bold px-3 py-1.5 rounded-lg shadow">
                                -{{ $game->discount_percent }}%
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Informações Principais -->
                <div class="flex-1 space-y-6">
                    
                    <!-- Título e Avaliação -->
                    <div>
                        <h1 class="text-3xl sm:text-4xl font-bold text-white mb-4">{{ $game->title }}</h1>
                        
                        <div class="flex flex-wrap items-center gap-4 mb-4">
                            @if($game->metacritic)
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-gray-300 dark:text-gray-400">Metacritic:</span>
                                    <span class="px-3 py-1 rounded-lg font-bold text-sm {{ $game->metacritic >= 80 ? 'bg-green-600' : ($game->metacritic >= 60 ? 'bg-yellow-600' : 'bg-red-600') }} text-white">
                                        {{ $game->metacritic }}
                                    </span>
                                </div>
                            @endif
                            
                            @if(isset($rawgData['rating']))
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-gray-300 dark:text-gray-400">RAWG:</span>
                                    <div class="flex items-center gap-1">
                                        <span class="text-yellow-400 font-bold">{{ number_format($rawgData['rating'], 1) }}</span>
                                        <div class="flex">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-4 h-4 {{ $i <= round($rawgData['rating']) ? 'text-yellow-400' : 'text-gray-400' }}" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @endfor
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Informações Básicas -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            @php
                                $headerDeveloper = $game->developer ?: ($rawgData['developers'][0]['name'] ?? null);
                                $headerPublisher = $rawgData['publishers'][0]['name'] ?? null;
                            @endphp
                            @if($headerDeveloper)
                                <div>
                                    <span class="text-gray-400 dark:text-gray-500">Desenvolvedor:</span>
                                    <span class="text-gray-200 dark:text-gray-300 font-medium">{{ $headerDeveloper }}</span>
                                </div>
                            @endif
                            
                            @if($game->release_date)
                                <div>
                                    <span class="text-gray-400 dark:text-gray-500">Lançamento:</span>
                                    <span class="text-gray-200 dark:text-gray-300 font-medium">{{ $game->release_date->format('d/m/Y') }}</span>
                                </div>
                            @elseif(!empty($rawgData['released']))
                                <div>
                                    <span class="text-gray-400 dark:text-gray-500">Lançamento:</span>
                                    <span class="text-gray-200 dark:text-gray-300 font-medium">{{ $rawgData['released'] }}</span>
                                </div>
                            @endif
                            
                            @if($headerPublisher)
                                <div>
                                    <span class="text-gray-400 dark:text-gray-500">Editora:</span>
                                    <span class="text-gray-200 dark:text-gray-300 font-medium">{{ $headerPublisher }}</span>
                                </div>
                            @endif
                            
                            @if(!empty($rawgData['playtime']))
                                <div>
                                    <span class="text-gray-400 dark:text-gray-500">Tempo de Jogo:</span>
                                    <span class="text-gray-200 dark:text-gray-300 font-medium">~{{ $rawgData['playtime'] }}h</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Preços e Loja -->
                    <div class="bg-black/20 dark:bg-black/40 rounded-xl p-6 border border-gray-600/30">
                        <div class="flex flex-wrap items-center gap-3 mb-4">
                            <span class="text-3xl font-bold text-green-400 dark:text-green-300">
                                <x-price :price="$game->sale_price" />
                            </span>
                            @if($game->normal_price > $game->sale_price)
                                <span class="text-xl text-gray-400 dark:text-gray-500 line-through">
                                    <x-price :price="$game->normal_price" />
                                </span>
                                <span class="bg-red-600 dark:bg-red-500 text-white px-3 py-1 rounded-lg font-bold">
                                    Economia: <x-price :price="$game->normal_price - $game->sale_price" />
                                </span>
                            @endif
                        </div>
                        
                        <div class="flex flex-wrap items-center gap-3 mb-4">
                            <span class="text-gray-300 dark:text-gray-400">Disponível na:</span>
                            <span class="text-sm font-bold px-3 py-1 rounded {{ $game->store_color }} text-white shadow">
                                {{ $game->store_name }}
                            </span>
                        </div>
                        
                        @if($game->deal_ends_at && $game->deal_ends_at->isFuture())
                            <div class="text-orange-400 dark:text-orange-300 text-sm mb-4">
                                ⏰ Oferta válida até: {{ $game->deal_ends_at->format('d/m/Y H:i') }}
                                <span class="font-bold">
                                    @if($game->deal_ends_at->isToday())
                                        (Expira hoje!)
                                    @elseif($game->deal_ends_at->isTomorrow())
                                        (Expira amanhã)
                                    @else
                                        ({{ $game->deal_ends_at->diffForHumans() }})
                                    @endif
                                </span>
                            </div>
                        @endif
                        
                        <!-- Botões de Ação -->
                        <div class="flex flex-col gap-3">
                            <a href="{{ $game->best_url }}" target="_blank" rel="noopener noreferrer"
                                class="bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800 text-white font-semibold px-6 py-3 rounded-lg shadow transition text-center flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                                Comprar na {{ $game->store_name }}
                            </a>
                            
                            <div class="flex flex-col sm:flex-row gap-3">
                                @auth
                                    @php
                                        $isInWishlist = auth()->user()->wishlist()->where('game_id', $game->id)->exists();
                                    @endphp
                                    <form method="POST" action="{{ route('wishlist.toggle', $game) }}" class="flex-1 wishlist-form" data-game-id="{{ $game->id }}">
                                        @csrf
                                        <button type="submit" 
                                            class="w-full px-4 py-2 rounded-lg shadow transition flex items-center justify-center gap-2 font-medium wishlist-btn {{ $isInWishlist ? 'bg-yellow-500 hover:bg-yellow-600 dark:bg-yellow-600 dark:hover:bg-yellow-700 text-white' : 'bg-gray-600 hover:bg-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-200' }}"
                                            data-in-wishlist="{{ $isInWishlist ? 'true' : 'false' }}">
                                            <svg class="w-4 h-4" fill="{{ $isInWishlist ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                            </svg>
                                            {{ $isInWishlist ? 'Na Wishlist' : 'Adicionar à Wishlist' }}
                                        </button>
                                    </form>
                                @else
                                    <button onclick="showLoginPrompt()" 
                                        class="flex-1 bg-gray-600 hover:bg-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-200 font-medium px-4 py-2 rounded-lg shadow transition text-center flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                        Adicionar à Wishlist
                                    </button>
                                @endauth
                                
                                <button onclick="shareGame()" 
                                    class="flex-1 bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800 text-white font-medium px-4 py-2 rounded-lg shadow transition text-center flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"/>
                                    </svg>
                                    Compartilhar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
        
        <!-- Grid de Conteúdo -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Coluna Principal -->
            <div class="lg:col-span-2 space-y-8 px-6 sm:px-8">
                
                <!-- Descrição e Detalhes -->
                <div class="bg-gray-800/50 dark:bg-gray-900/50 rounded-xl p-6 border border-gray-600/30">
                    <h2 class="text-2xl font-bold text-white mb-4">Sobre o Jogo</h2>
                    
                    @php
                        $catalogDescription = $rawgData['description_raw'] ?? null;
                        $translatedDesc = $game->getTranslatedDescription();
                        $translation = $game->getTranslation('description');
                        $hasTranslation = $translation !== null;
                        $originalDescription = $translatedDesc
                            ? ($game->description ?: $catalogDescription)
                            : ($game->description && ! str_starts_with((string) $game->description, 'Oferta encontrada')
                                ? $game->description
                                : $catalogDescription);
                        $originalDescription = $originalDescription ?: '';

                        if ($hasTranslation && $translatedDesc) {
                            $displayDescription = $translatedDesc;
                            $isTranslated = true;
                            $translationMethod = $translation->translation_method;
                            $confidence = $translation->confidence_score;
                        } else {
                            $displayDescription = $originalDescription;
                            $isTranslated = false;
                            $translationMethod = null;
                            $confidence = null;
                        }

                        $shortDescription = \Illuminate\Support\Str::limit($displayDescription, 500);
                        $hasCatalogHtml = ! empty($rawgData['description_html']) && ! $isTranslated;
                    @endphp

                    @if($displayDescription || $hasCatalogHtml)
                        <div class="prose prose-invert max-w-none">
                            @if($isTranslated)
                                <div class="bg-green-900/20 border border-green-500/30 rounded-lg p-4 mb-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span class="text-green-300 text-sm font-medium">
                                            Descrição traduzida para português
                                            @if($translationMethod === 'google_api')
                                                (Google Translate)
                                            @elseif($translationMethod === 'local_keywords')
                                                (Tradução automática)
                                            @endif
                                        </span>
                                    </div>
                                    @if($confidence)
                                        <div class="flex items-center gap-2">
                                            <span class="text-green-200 text-xs">Confiabilidade:</span>
                                            <div class="flex-1 bg-gray-700 rounded-full h-2 max-w-32">
                                                <div class="bg-green-500 h-2 rounded-full" style="width: {{ $confidence * 100 }}%"></div>
                                            </div>
                                            <span class="text-green-200 text-xs">{{ round($confidence * 100) }}%</span>
                                        </div>
                                    @endif
                                </div>
                            @elseif(($rawgData['source'] ?? null) === 'steam')
                                <div class="mb-3 text-xs text-gray-500">Detalhes via Steam Store (PT-BR)</div>
                            @endif

                            @if($hasCatalogHtml)
                                <div class="steam-description text-gray-300 dark:text-gray-400 leading-relaxed mb-4 space-y-3 [&_img]:rounded-lg [&_img]:max-w-full">
                                    {!! $rawgData['description_html'] !!}
                                </div>
                            @else
                                <p class="text-gray-300 dark:text-gray-400 leading-relaxed mb-4 whitespace-pre-line">
                                    {{ $shortDescription }}
                                </p>

                                @if(strlen($displayDescription) > 500)
                                    <div id="full-description" class="hidden">
                                        <p class="text-gray-300 dark:text-gray-400 leading-relaxed whitespace-pre-line">
                                            {{ $displayDescription }}
                                        </p>
                                    </div>
                                    <button type="button" onclick="toggleDescription()"
                                            class="text-red-400 hover:text-red-300 font-medium"
                                            id="description-toggle">
                                        Ler mais
                                    </button>
                                @endif
                            @endif
                        </div>
                    @else
                        <div class="bg-gray-700/30 rounded-lg p-6 text-center">
                            <p class="text-gray-400 dark:text-gray-500 mb-2">Descrição ainda não disponível para este jogo.</p>
                            <p class="text-gray-500 dark:text-gray-600 text-sm">Pode ser um título sem página Steam correspondente.</p>
                        </div>
                    @endif
                    
                    <!-- Categorias / gêneros -->
                    @php
                        $genreNames = $game->categories->pluck('name');
                        if ($genreNames->isEmpty() && !empty($rawgData['genres'])) {
                            $genreNames = collect($rawgData['genres'])->pluck('name')->filter();
                        }
                    @endphp
                    @if($genreNames->isNotEmpty())
                        <div class="mt-6">
                            <h3 class="text-lg font-semibold text-white mb-3">Gêneros</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($genreNames as $genreName)
                                    <span class="bg-gray-700/80 dark:bg-gray-700/60 text-gray-200 dark:text-gray-300 px-3 py-1 rounded-lg text-sm backdrop-blur-sm border border-gray-600/30">
                                        {{ translateGenre($genreName) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <!-- Plataformas (do catálogo) -->
                    @if(isset($rawgData['platforms']) && count($rawgData['platforms']) > 0)
                        <div class="mt-6">
                            <h3 class="text-lg font-semibold text-white mb-3">Plataformas</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($rawgData['platforms'] as $platform)
                                    <span class="bg-blue-600/20 dark:bg-blue-600/30 text-blue-300 dark:text-blue-400 px-3 py-1 rounded-lg text-sm border border-blue-500/30">
                                        {{ translatePlatform($platform['platform']['name'] ?? '') }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Trailers / vídeos -->
                @if(!empty($rawgData['movies']))
                    <div class="bg-gray-800/50 dark:bg-gray-900/50 rounded-xl p-6 border border-gray-600/30">
                        <h2 class="text-2xl font-bold text-white mb-4">Trailers</h2>
                        <div class="grid grid-cols-1 gap-4">
                            @foreach(array_slice($rawgData['movies'], 0, 3) as $movie)
                                <div class="rounded-xl overflow-hidden border border-gray-700/50 bg-black">
                                    <video
                                        class="w-full aspect-video bg-black"
                                        controls
                                        preload="metadata"
                                        @if(!empty($movie['poster'])) poster="{{ $movie['poster'] }}" @endif
                                    >
                                        @if(!empty($movie['mp4']))
                                            <source src="{{ $movie['mp4'] }}" type="video/mp4">
                                        @endif
                                        @if(!empty($movie['webm']))
                                            <source src="{{ $movie['webm'] }}" type="video/webm">
                                        @endif
                                    </video>
                                    @if(!empty($movie['name']))
                                        <div class="px-3 py-2 text-sm text-gray-300">{{ $movie['name'] }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                <!-- Screenshots -->
                @if(isset($rawgData['screenshots']) && count($rawgData['screenshots']) > 0)
                    <div class="bg-gray-800/50 dark:bg-gray-900/50 rounded-xl p-6 border border-gray-600/30">
                        <h2 class="text-2xl font-bold text-white mb-4">Capturas de Tela</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach(array_slice($rawgData['screenshots'], 0, 8) as $screenshot)
                                <div class="relative group cursor-pointer" onclick="openImageModal('{{ $screenshot['image'] }}')">
                                    <img src="{{ $screenshot['image'] }}"
                                         alt="Screenshot de {{ $game->title }}"
                                         loading="lazy"
                                         class="w-full h-48 object-cover rounded-lg transition-transform group-hover:scale-[1.02]" />
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors rounded-lg flex items-center justify-center">
                                        <svg class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                        </svg>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif                
                <!-- Outras Ofertas -->
                @if(isset($relatedOffers) && $relatedOffers->isNotEmpty())
                    <div class="bg-gray-800/50 dark:bg-gray-900/50 rounded-xl p-6 border border-gray-600/30">
                        <h2 class="text-2xl font-bold text-white mb-4">Outras Ofertas</h2>
                        <div class="space-y-3">
                            @foreach($relatedOffers as $offer)
                                <div class="flex items-center justify-between p-4 bg-gray-700/30 dark:bg-gray-800/30 rounded-lg border border-gray-600/20">
                                    <div class="flex items-center gap-4">
                                        <img src="{{ $offer->optimized_image }}" alt="{{ $offer->title }}" 
                                             class="w-24 h-[45px] object-cover object-center rounded-lg shrink-0" />
                                        <div>
                                            <h3 class="font-semibold text-white">{{ $offer->title }}</h3>
                                            <p class="text-sm text-gray-400 dark:text-gray-500">{{ $offer->store_name }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-bold text-green-400">
                                            <x-price :price="$offer->sale_price" />
                                        </div>
                                        @if($offer->discount_percent > 0)
                                            <div class="text-sm text-red-400">
                                                -{{ $offer->discount_percent }}%
                                            </div>
                                        @endif
                                    </div>
                                    <a href="{{ route('games.show', $offer) }}" 
                                       class="bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800 text-white px-4 py-2 rounded-lg transition">
                                        Ver
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            
            <!-- Sidebar -->
            <div class="space-y-6 px-6 sm:px-8">
                
                <!-- Especificações Técnicas -->
                <div class="bg-gray-800/50 dark:bg-gray-900/50 rounded-xl p-6 border border-gray-600/30">
                    <h3 class="text-xl font-bold text-white mb-4">Especificações</h3>
                    <div class="space-y-3 text-sm">
                        @php
                            $specDeveloper = $game->developer ?: ($rawgData['developers'][0]['name'] ?? null);
                            $specPublisher = $rawgData['publishers'][0]['name'] ?? null;
                            $specReleased = $rawgData['released'] ?? null;
                            $specMetacritic = $game->metacritic ?: ($rawgData['metacritic'] ?? null);
                            $hasAnySpec = $specDeveloper || $specPublisher || $game->release_date || $specReleased
                                || !empty($rawgData['esrb_rating']['name']) || !empty($rawgData['playtime'])
                                || $specMetacritic || !empty($rawgData['website']) || !empty($rawgData['genres']);
                        @endphp

                        @if($specDeveloper)
                            <div class="flex justify-between gap-3">
                                <span class="text-gray-400 dark:text-gray-500 shrink-0">Desenvolvedor:</span>
                                <span class="text-gray-200 dark:text-gray-300 text-right">{{ $specDeveloper }}</span>
                            </div>
                        @endif

                        @if($specPublisher)
                            <div class="flex justify-between gap-3">
                                <span class="text-gray-400 dark:text-gray-500 shrink-0">Editora:</span>
                                <span class="text-gray-200 dark:text-gray-300 text-right">{{ $specPublisher }}</span>
                            </div>
                        @endif

                        @if($game->release_date)
                            <div class="flex justify-between gap-3">
                                <span class="text-gray-400 dark:text-gray-500">Lançamento:</span>
                                <span class="text-gray-200 dark:text-gray-300">{{ $game->release_date->format('d/m/Y') }}</span>
                            </div>
                        @elseif($specReleased)
                            <div class="flex justify-between gap-3">
                                <span class="text-gray-400 dark:text-gray-500">Lançamento:</span>
                                <span class="text-gray-200 dark:text-gray-300 text-right">{{ $specReleased }}</span>
                            </div>
                        @endif

                        @if($specMetacritic)
                            <div class="flex justify-between gap-3">
                                <span class="text-gray-400 dark:text-gray-500">Metacritic:</span>
                                <span class="text-gray-200 dark:text-gray-300 font-semibold">{{ $specMetacritic }}</span>
                            </div>
                        @endif

                        @if(!empty($rawgData['genres']))
                            <div class="flex justify-between gap-3">
                                <span class="text-gray-400 dark:text-gray-500 shrink-0">Gêneros:</span>
                                <span class="text-gray-200 dark:text-gray-300 text-right">
                                    {{ collect($rawgData['genres'])->pluck('name')->filter()->take(4)->implode(', ') }}
                                </span>
                            </div>
                        @endif

                        @if(isset($rawgData['esrb_rating']['name']))
                            <div class="flex justify-between gap-3">
                                <span class="text-gray-400 dark:text-gray-500">Classificação:</span>
                                <span class="text-gray-200 dark:text-gray-300">{{ translateESRBRating($rawgData['esrb_rating']['name']) }}</span>
                            </div>
                        @endif

                        @if(!empty($rawgData['playtime']))
                            <div class="flex justify-between gap-3">
                                <span class="text-gray-400 dark:text-gray-500">Tempo Médio:</span>
                                <span class="text-gray-200 dark:text-gray-300">{{ $rawgData['playtime'] }} horas</span>
                            </div>
                        @endif

                        @if(!empty($rawgData['website']))
                            <div class="pt-2">
                                <a href="{{ $rawgData['website'] }}" target="_blank" rel="noopener"
                                   class="text-red-400 hover:text-red-300 text-sm underline underline-offset-2">
                                    Site oficial
                                </a>
                            </div>
                        @endif

                        @if(! $hasAnySpec)
                            <p class="text-gray-500 text-sm">Sem especificação catalogada para este título ainda.</p>
                        @endif
                    </div>
                </div>

                @if(!empty($rawgData['pc_requirements']['minimum']) || !empty($rawgData['pc_requirements']['recommended']))
                    <div class="bg-gray-800/50 dark:bg-gray-900/50 rounded-xl p-6 border border-gray-600/30">
                        <h3 class="text-xl font-bold text-white mb-4">Requisitos (PC)</h3>
                        <div class="space-y-4 text-sm text-gray-300 steam-requirements [&_ul]:list-disc [&_ul]:pl-5 [&_strong]:text-white">
                            @if(!empty($rawgData['pc_requirements']['minimum']))
                                <div>
                                    <h4 class="text-white font-semibold mb-2">Mínimos</h4>
                                    {!! $rawgData['pc_requirements']['minimum'] !!}
                                </div>
                            @endif
                            @if(!empty($rawgData['pc_requirements']['recommended']))
                                <div>
                                    <h4 class="text-white font-semibold mb-2">Recomendados</h4>
                                    {!! $rawgData['pc_requirements']['recommended'] !!}
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                
                <!-- Jogos Similares -->
                @if(isset($similarGames) && $similarGames->isNotEmpty())
                    <div class="bg-gray-800/50 dark:bg-gray-900/50 rounded-xl p-6 border border-gray-600/30">
                        <h3 class="text-xl font-bold text-white mb-1">Jogos Similares</h3>
                        <p class="text-xs text-gray-500 mb-4">Com gêneros em comum</p>
                        <div class="space-y-3">
                            @foreach($similarGames->take(6) as $similar)
                                <a href="{{ route('games.show', $similar) }}"
                                   class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-700/30 dark:hover:bg-gray-800/30 transition group">
                                    <img src="{{ $similar->optimized_image }}" alt="{{ $similar->title }}"
                                         class="w-24 h-[45px] object-cover object-center rounded shrink-0"
                                         loading="lazy"
                                         onerror="this.onerror=null; this.src='{{ $similar->placeholderCoverUrl() }}';" />
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-medium text-white group-hover:text-red-400 transition truncate">
                                            {{ $similar->title }}
                                        </h4>
                                        <div class="flex items-center gap-2 text-sm">
                                            <span class="text-green-400"><x-price :price="$similar->sale_price" /></span>
                                            @if($similar->discount_percent > 0)
                                                <span class="text-red-400">-{{ $similar->discount_percent }}%</span>
                                            @endif
                                        </div>
                                        @if($similar->categories->isNotEmpty())
                                            <div class="text-[10px] text-gray-500 truncate mt-0.5">
                                                {{ $similar->categories->take(2)->pluck('name')->implode(' · ') }}
                                            </div>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif            </div>
        </div>
    </div>
</div>

<!-- Modal para imagens -->
<div id="imageModal" class="fixed inset-0 bg-black/80 hidden z-50 flex items-center justify-center p-4" onclick="closeImageModal()">
    <div class="relative max-w-4xl max-h-full">
        <img id="modalImage" src="" alt="Screenshot" class="w-full h-auto rounded-lg shadow-lg" />
        <button onclick="closeImageModal()" 
                class="absolute top-4 right-4 bg-black/50 text-white rounded-full p-2 hover:bg-black/70 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>

<script>
function toggleDescription() {
    const fullDesc = document.getElementById('full-description');
    const toggleBtn = document.getElementById('description-toggle');
    
    if (fullDesc.classList.contains('hidden')) {
        fullDesc.classList.remove('hidden');
        toggleBtn.textContent = 'Ler menos';
    } else {
        fullDesc.classList.add('hidden');
        toggleBtn.textContent = 'Ler mais';
    }
}

function toggleOriginalDescription() {
    const originalDesc = document.getElementById('original-description');
    const toggleBtn = document.getElementById('original-toggle');
    
    if (originalDesc.classList.contains('hidden')) {
        originalDesc.classList.remove('hidden');
        toggleBtn.innerHTML = `
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
            </svg>
            Ocultar texto original
        `;
    } else {
        originalDesc.classList.add('hidden');
        toggleBtn.innerHTML = `
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
            </svg>
            Ver texto original em inglês
        `;
    }
}

function openImageModal(src) {
    document.getElementById('modalImage').src = src;
    document.getElementById('imageModal').classList.remove('hidden');
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
}

function shareGame() {
    if (navigator.share) {
        navigator.share({
            title: '{{ $game->title }}',
            text: 'Confira esta oferta incrível!',
            url: window.location.href
        });
    } else {
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Link copiado para a área de transferência!');
        });
    }
}

function showLoginPrompt() {
    alert('Você precisa estar logado para utilizar esta funcionalidade.');
    window.location.href = '{{ route("login") }}';
}
</script>
@endsection