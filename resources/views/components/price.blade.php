@props(['price', 'currency' => null, 'user' => null])

@php
    $user = $user ?: (auth()->check() ? auth()->user() : null);
    $currency = $currency ?: ($user ? $user->currency : config('app.default_currency'));
    $formattedPrice = formatPrice($price, $user);
@endphp

<span {{ $attributes }}>{{ $formattedPrice }}</span>
