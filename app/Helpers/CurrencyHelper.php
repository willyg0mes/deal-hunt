<?php

use Illuminate\Support\Facades\Auth;

if (!function_exists('formatPrice')) {
    /**
     * Formatar preço convertendo de USD para a moeda do usuário
     */
    function formatPrice(float $usdPrice, ?App\Models\User $user = null): string
    {
        $user = $user ?: (Auth::check() ? Auth::user() : null);
        
        // Se não há usuário logado, usar configurações padrão
        if (!$user) {
            $currency = config('app.default_currency', 'BRL');
            $locale = config('app.default_locale', 'pt-BR');
        } else {
            $currency = $user->currency ?? 'BRL';
            $locale = $user->locale ?? 'pt-BR';
        }

        $conversionService = app(\App\Services\CurrencyConversionService::class);
        
        // Converter de USD para moeda do usuário
        $convertedPrice = $conversionService->convertFromUSD($usdPrice, $currency);
        
        // Formatar de acordo com locale
        return $conversionService->formatCurrency($convertedPrice, $currency, $locale);
    }
}

if (!function_exists('formatPriceSimple')) {
    /**
     * Formatar preço simples (sem consultar banco de dados)
     */
    function formatPriceSimple(float $usdPrice, string $currency = 'BRL', string $locale = 'pt-BR'): string
    {
        $conversionService = app(\App\Services\CurrencyConversionService::class);
        
        // Converter de USD para moeda especificada
        $convertedPrice = $conversionService->convertFromUSD($usdPrice, $currency);
        
        // Formatar de acordo com locale
        return $conversionService->formatCurrency($convertedPrice, $currency, $locale);
    }
}

if (!function_exists('convertPrice')) {
    /**
     * Converter preço de USD para outra moeda (retorna apenas o valor numérico)
     */
    function convertPrice(float $usdPrice, string $targetCurrency = 'BRL'): float
    {
        $conversionService = app(\App\Services\CurrencyConversionService::class);
        return $conversionService->convertFromUSD($usdPrice, $targetCurrency);
    }
}

if (!function_exists('getCurrencySymbol')) {
    /**
     * Obter símbolo da moeda
     */
    function getCurrencySymbol(string $currency = 'BRL'): string
    {
        $conversionService = app(\App\Services\CurrencyConversionService::class);
        $info = $conversionService->getCurrencyInfo($currency);
        return $info['symbol'] ?? 'R$';
    }
}
