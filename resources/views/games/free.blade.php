@extends('layouts.app')

@section('title', 'Jogos grátis agora | '.config('app.name', 'DealHunt'))
@section('meta_description', 'Lista de jogos gratuitos e free-to-play atualizada. Pegue grátis no DealHunt.')
@section('canonical', route('games.free'))

@section('content')
<div class="max-w-[1600px] mx-auto px-4 sm:px-6 py-8">
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">Jogos gratuitos</h1>
        <p class="text-sm text-gray-400 mt-1">Gratuitos de verdade — sem tempo limite</p>
    </div>

    @include('partials.games-filters', [
        'ajax' => false,
        'action' => route('games.free'),
        'stores' => $stores,
        'categories' => $categories,
        'showFree' => false,
        'showDiscount' => false,
        'showPrice' => false,
    ])

    <div id="games-results">
        @include('partials.games-list', ['games' => $games])
    </div>
</div>
@endsection
