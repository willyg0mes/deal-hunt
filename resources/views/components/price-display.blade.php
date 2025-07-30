@props(['normalPrice', 'salePrice', 'user' => null])

@php
    $user = $user ?: (auth()->check() ? auth()->user() : null);
    $showDiscount = $normalPrice > $salePrice;
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col']) }}>
    <span class="text-lg font-bold text-green-600 dark:text-green-400">
        <x-price :price="$salePrice" :user="$user" />
    </span>
    
    @if($showDiscount)
        <span class="text-sm text-gray-400 dark:text-gray-500 line-through">
            <x-price :price="$normalPrice" :user="$user" />
        </span>
        
        @php
            $savings = $normalPrice - $salePrice;
            $discountPercent = round((($normalPrice - $salePrice) / $normalPrice) * 100);
        @endphp
        
        <span class="text-xs text-green-600 dark:text-green-400">
            Economia: <x-price :price="$savings" :user="$user" /> ({{ $discountPercent }}%)
        </span>
    @endif
</div>
