<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CurrencyConversionService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CurrencyController extends Controller
{
    protected $currencyService;

    public function __construct(CurrencyConversionService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    /**
     * Exibe o painel de administração de moedas
     */
    public function index()
    {
        try {
            // Obter taxas atuais
            $rates = $this->currencyService->getExchangeRates();
            
            // Estatísticas de usuários por moeda
            $userStats = User::selectRaw('currency, COUNT(*) as count, AVG(currency_rate) as avg_rate')
                ->groupBy('currency')
                ->get();

            // Histórico de atualizações (últimas 24h)
            $lastUpdate = Cache::get('currency_rates_updated_at');
            $cacheAge = $lastUpdate ? now()->diffInMinutes($lastUpdate) : null;

            // Usuários com taxas desatualizadas (diferença > 5% da taxa atual)
            $outdatedUsers = collect();
            foreach ($userStats as $stat) {
                if (isset($rates[$stat->currency])) {
                    $currentRate = $rates[$stat->currency];
                    $avgUserRate = $stat->avg_rate;
                    $difference = abs($currentRate - $avgUserRate) / $currentRate * 100;
                    
                    if ($difference > 5) {
                        $outdatedUsers->push([
                            'currency' => $stat->currency,
                            'users_count' => $stat->count,
                            'avg_user_rate' => $avgUserRate,
                            'current_rate' => $currentRate,
                            'difference_percent' => round($difference, 2)
                        ]);
                    }
                }
            }

            return view('admin.currency.index', compact(
                'rates',
                'userStats',
                'lastUpdate',
                'cacheAge',
                'outdatedUsers'
            ));

        } catch (\Exception $e) {
            Log::error('Erro ao carregar painel de moedas: ' . $e->getMessage());
            
            return back()->with('error', 'Erro ao carregar informações de moedas: ' . $e->getMessage());
        }
    }

    /**
     * Atualiza as taxas de câmbio para todos os usuários
     */
    public function updateRates(Request $request)
    {
        $request->validate([
            'force' => 'nullable|boolean',
            'user_id' => 'nullable|integer|exists:users,id'
        ]);

        try {
            $force = $request->boolean('force');
            $userId = $request->input('user_id');

            // Preparar comando
            $command = 'currency:update-rates';
            $parameters = [];
            
            if ($force) {
                $parameters['--force'] = true;
            }
            
            if ($userId) {
                $parameters['--user'] = $userId;
            }

            // Executar comando
            $exitCode = Artisan::call($command, $parameters);
            $output = Artisan::output();

            if ($exitCode === 0) {
                $message = $userId ? 
                    "Taxas atualizadas com sucesso para o usuário #{$userId}!" : 
                    "Taxas atualizadas com sucesso para todos os usuários!";
                
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'output' => $output
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao atualizar taxas de câmbio',
                    'output' => $output
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Erro ao atualizar taxas: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtém taxas atuais sem atualizar cache
     */
    public function getCurrentRates()
    {
        try {
            $rates = $this->currencyService->getExchangeRates();
            $lastUpdate = Cache::get('currency_rates_updated_at');
            
            return response()->json([
                'success' => true,
                'rates' => $rates,
                'last_update' => $lastUpdate?->toISOString(),
                'cache_age_minutes' => $lastUpdate ? now()->diffInMinutes($lastUpdate) : null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter taxas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Força atualização do cache de taxas
     */
    public function refreshCache()
    {
        try {
            $this->currencyService->refreshRates();
            $rates = $this->currencyService->getExchangeRates();
            
            return response()->json([
                'success' => true,
                'message' => 'Cache de taxas atualizado com sucesso!',
                'rates' => $rates,
                'updated_at' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao atualizar cache: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estatísticas detalhadas sobre uso de moedas
     */
    public function getStats()
    {
        try {
            // Estatísticas por moeda
            $currencyStats = User::selectRaw('
                currency, 
                COUNT(*) as users_count,
                AVG(currency_rate) as avg_rate,
                MIN(currency_rate) as min_rate,
                MAX(currency_rate) as max_rate,
                STDDEV(currency_rate) as rate_stddev
            ')
            ->groupBy('currency')
            ->get();

            // Usuários com taxas muito desatualizadas
            $rates = $this->currencyService->getExchangeRates();
            $problematicUsers = collect();

            foreach ($currencyStats as $stat) {
                if (isset($rates[$stat->currency])) {
                    $currentRate = $rates[$stat->currency];
                    $users = User::where('currency', $stat->currency)
                        ->whereRaw('ABS(currency_rate - ?) / ? > 0.1', [$currentRate, $currentRate])
                        ->get(['id', 'name', 'email', 'currency_rate']);
                    
                    foreach ($users as $user) {
                        $difference = abs($user->currency_rate - $currentRate) / $currentRate * 100;
                        $problematicUsers->push([
                            'user_id' => $user->id,
                            'user_name' => $user->name,
                            'user_email' => $user->email,
                            'currency' => $stat->currency,
                            'user_rate' => $user->currency_rate,
                            'current_rate' => $currentRate,
                            'difference_percent' => round($difference, 2)
                        ]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'currency_stats' => $currencyStats,
                'problematic_users' => $problematicUsers->sortByDesc('difference_percent')->take(20),
                'total_users' => User::count(),
                'supported_currencies' => array_keys($rates)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter estatísticas: ' . $e->getMessage()
            ], 500);
        }
    }
}
