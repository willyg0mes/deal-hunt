<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CurrencyConversionService
{
    private const API_URL = 'https://api.exchangerate-api.com/v4/latest/USD';
    private const CACHE_KEY = 'exchange_rates';
    private const CACHE_DURATION = 3600; // 1 hora

    /**
     * Moedas suportadas com símbolos
     */
    private const SUPPORTED_CURRENCIES = [
        'BRL' => ['symbol' => 'R$', 'name' => 'Real Brasileiro'],
        'USD' => ['symbol' => '$', 'name' => 'Dólar Americano'],
        'EUR' => ['symbol' => '€', 'name' => 'Euro'],
        'GBP' => ['symbol' => '£', 'name' => 'Libra Esterlina'],
        'CAD' => ['symbol' => 'C$', 'name' => 'Dólar Canadense'],
        'AUD' => ['symbol' => 'A$', 'name' => 'Dólar Australiano'],
        'JPY' => ['symbol' => '¥', 'name' => 'Iene Japonês'],
    ];

    /**
     * Obtém as taxas de câmbio atualizadas
     */
    public function getExchangeRates(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_DURATION, function () {
            try {
                $response = Http::timeout(10)->get(self::API_URL);
                
                if ($response->successful()) {
                    $data = $response->json();
                    return $data['rates'] ?? [];
                }
            } catch (\Exception $e) {
                Log::warning('Erro ao buscar taxas de câmbio: ' . $e->getMessage());
            }

            // Fallback com taxas aproximadas (atualize conforme necessário)
            return [
                'BRL' => 5.20,
                'EUR' => 0.85,
                'GBP' => 0.75,
                'CAD' => 1.25,
                'AUD' => 1.35,
                'JPY' => 110.0,
                'USD' => 1.0
            ];
        });
    }

    /**
     * Converte valor de USD para a moeda especificada
     */
    public function convertFromUSD(float $usdAmount, string $targetCurrency): float
    {
        if ($targetCurrency === 'USD') {
            return $usdAmount;
        }

        $rates = $this->getExchangeRates();
        $rate = $rates[$targetCurrency] ?? 1.0;
        
        return round($usdAmount * $rate, 2);
    }

    /**
     * Converte valor de uma moeda para outra
     */
    public function convert(float $amount, string $fromCurrency, string $toCurrency): float
    {
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        $rates = $this->getExchangeRates();
        
        // Converter primeiro para USD (base)
        $usdAmount = $fromCurrency === 'USD' ? $amount : $amount / ($rates[$fromCurrency] ?? 1.0);
        
        // Depois converter para a moeda de destino
        return $this->convertFromUSD($usdAmount, $toCurrency);
    }

    /**
     * Formata valor monetário de acordo com a moeda e locale
     */
    public function formatCurrency(float $amount, string $currency, string $locale = 'pt-BR'): string
    {
        $currencyInfo = self::SUPPORTED_CURRENCIES[$currency] ?? self::SUPPORTED_CURRENCIES['BRL'];
        $symbol = $currencyInfo['symbol'];

        // Formatação específica para cada locale
        switch ($locale) {
            case 'pt-BR':
                return $symbol . ' ' . number_format($amount, 2, ',', '.');
            case 'en-US':
                return $symbol . number_format($amount, 2, '.', ',');
            default:
                return $symbol . ' ' . number_format($amount, 2, '.', ',');
        }
    }

    /**
     * Obtém informações da moeda
     */
    public function getCurrencyInfo(string $currency): array
    {
        return self::SUPPORTED_CURRENCIES[$currency] ?? self::SUPPORTED_CURRENCIES['BRL'];
    }

    /**
     * Lista todas as moedas suportadas
     */
    public function getSupportedCurrencies(): array
    {
        return self::SUPPORTED_CURRENCIES;
    }

    /**
     * Atualiza a taxa de conversão para um usuário específico
     */
    public function updateUserCurrencyRate(\App\Models\User $user): void
    {
        if ($user->currency === 'USD') {
            $user->update(['currency_rate' => 1.0000]);
            return;
        }

        $rates = $this->getExchangeRates();
        $rate = $rates[$user->currency] ?? 1.0;
        
        $user->update(['currency_rate' => $rate]);
    }

    /**
     * Força atualização das taxas removendo do cache
     */
    public function refreshRates(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
