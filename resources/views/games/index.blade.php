@extends('layouts.app')

@section('title', 'Ofertas de jogos | '.config('app.name', 'DealHunt'))
@section('meta_description', 'Catálogo de ofertas de games com filtros por loja, categoria, preço e desconto. Atualizado no DealHunt.')
@section('canonical', route('games.index'))

@section('content')
<div class="max-w-[1600px] mx-auto px-4 sm:px-6 py-8">
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">Todos os jogos</h1>
        <p class="text-sm text-gray-400 mt-1">Catálogo completo com filtros avançados</p>
    </div>

    @include('partials.games-filters', [
        'ajax' => false,
        'action' => route('games.index'),
        'stores' => $stores,
        'categories' => $categories,
        'showFree' => true,
        'showDiscount' => true,
        'showPrice' => true,
    ])

    <div id="games-results">
        @include('partials.games-list', ['games' => $games])
    </div>
</div>
@endsection
