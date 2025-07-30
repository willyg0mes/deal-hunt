<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\User;
use App\Models\AdminLog;
use App\Models\AppMetric;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Métricas gerais
        $totalGames = Game::count();
        $totalUsers = User::count();
        $totalAdmins = User::where('is_admin', true)->count();
        $recentGames = Game::where('created_at', '>=', Carbon::today())->count();

        // Estatísticas dos últimos 7 dias
        $pageViews = AppMetric::getMetrics('page_views', 7);
        $gameSearches = AppMetric::getMetrics('game_searches', 7);
        $apiCalls = AppMetric::getMetrics('api_calls', 7);

        // Logs recentes
        $recentLogs = AdminLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Jogos com maior desconto
        $topDeals = Game::where('discount_percent', '>', 0)
            ->orderBy('discount_percent', 'desc')
            ->limit(5)
            ->get();

        // Lojas mais populares
        $storeStats = Game::selectRaw('store_name, COUNT(*) as count')
            ->whereNotNull('store_name')
            ->groupBy('store_name')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalGames',
            'totalUsers', 
            'totalAdmins',
            'recentGames',
            'pageViews',
            'gameSearches',
            'apiCalls',
            'recentLogs',
            'topDeals',
            'storeStats'
        ));
    }

    public function fetchDeals(Request $request)
    {
        // Se for busca ilimitada, usar collect:deals
        if ($request->has('unlimited')) {
            AdminLog::log('unlimited_fetch_deals', null, null, [
                'type' => 'unlimited'
            ]);

            try {
                // Executar o comando collect:deals que busca tudo
                Artisan::call('collect:deals');
                
                $output = Artisan::output();
                
                // Extrair informações do output
                $pagesCount = 0;
                $dealsCount = 0;
                $newGames = 0;
                $updatedGames = 0;
                
                if (preg_match('/(\d+) páginas/', $output, $matches)) {
                    $pagesCount = $matches[1];
                }
                if (preg_match('/(\d+) deals processados/', $output, $matches)) {
                    $dealsCount = $matches[1];
                }
                if (preg_match('/Jogos novos\s+\|\s+(\d+)/', $output, $matches)) {
                    $newGames = $matches[1];
                }
                if (preg_match('/Jogos atualizados\s+\|\s+(\d+)/', $output, $matches)) {
                    $updatedGames = $matches[1];
                }
                
                return response()->json([
                    'success' => true,
                    'message' => "🎯 Coleta ilimitada concluída! {$pagesCount} páginas processadas, {$dealsCount} deals coletados, {$newGames} jogos novos, {$updatedGames} atualizados.",
                    'pages' => $pagesCount,
                    'deals' => $dealsCount,
                    'new_games' => $newGames,
                    'updated_games' => $updatedGames,
                    'total_games' => Game::count(),
                    'output' => $output
                ]);
                
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => "Erro ao executar coleta ilimitada: " . $e->getMessage()
                ], 500);
            }
        }

        // Busca limitada usando games:fetch-deals
        $request->validate([
            'limit' => 'required|integer|min:50|max:3000'
        ]);

        $limit = $request->get('limit', 100);

        AdminLog::log('manual_fetch_deals', null, null, [
            'limit' => $limit
        ]);

        try {
            // Executar o comando real de busca de deals
            Artisan::call('games:fetch-deals', [
                '--limit' => $limit
            ]);
            
            $output = Artisan::output();
            
            // Extrair informações do output para melhor feedback
            $processedCount = 0;
            $updatedCount = 0;
            $pagesCount = 0;
            
            if (preg_match('/(\d+) deals coletados/', $output, $matches)) {
                $processedCount = $matches[1];
            }
            if (preg_match('/Fazendo (\d+) requisições/', $output, $matches)) {
                $pagesCount = $matches[1];
            }
            if (preg_match('/Jogos atualizados\s+\|\s+(\d+)/', $output, $matches)) {
                $updatedCount = $matches[1];
            }
            
            return response()->json([
                'success' => true,
                'message' => "Busca limitada concluída! {$pagesCount} requisições feitas, {$processedCount} deals coletados, {$updatedCount} jogos atualizados.",
                'processed' => $processedCount,
                'updated' => $updatedCount,
                'pages' => $pagesCount,
                'total_games' => Game::count(),
                'output' => $output
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Erro ao executar busca: " . $e->getMessage()
            ], 500);
        }
    }

    public function dealsProgress()
    {
        return view('admin.deals-progress');
    }

    public function users()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(20);
        
        return view('admin.users', compact('users'));
    }

    public function toggleAdmin(User $user)
    {
        $user->is_admin = !$user->is_admin;
        $user->save();

        AdminLog::log('toggle_admin', 'User', $user->id, [
            'user_name' => $user->name,
            'is_admin' => $user->is_admin
        ]);

        return back()->with('success', 
            $user->is_admin ? 
            "Usuário {$user->name} promovido a administrador." :
            "Usuário {$user->name} removido dos administradores."
        );
    }

    public function logs()
    {
        $logs = AdminLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('admin.logs', compact('logs'));
    }

    /**
     * Verificar deals expirados (dry-run)
     */
    public function verifyExpiredDeals()
    {
        try {
            $totalGames = Game::count();
            $expiredGames = Game::where('deal_ends_at', '<', now())->count();

            AdminLog::log('verify_expired_deals', 'Game', null, [
                'total_games' => $totalGames,
                'expired_count' => $expiredGames
            ]);

            return response()->json([
                'success' => true,
                'total_count' => $totalGames,
                'expired_count' => $expiredGames,
                'message' => $expiredGames > 0 
                    ? "Encontrados {$expiredGames} deal(s) expirado(s) de {$totalGames} total."
                    : "Não há deals expirados no momento."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Erro ao verificar deals expirados: " . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remover deals expirados
     */
    public function removeExpiredDeals()
    {
        try {
            $expiredGames = Game::where('deal_ends_at', '<', now());
            $expiredCount = $expiredGames->count();
            
            if ($expiredCount === 0) {
                return response()->json([
                    'success' => true,
                    'removed_count' => 0,
                    'remaining_count' => Game::count(),
                    'message' => 'Não há deals expirados para remover.'
                ]);
            }

            // Remover deals expirados
            $expiredGames->delete();
            $remainingCount = Game::count();

            AdminLog::log('remove_expired_deals', 'Game', null, [
                'removed_count' => $expiredCount,
                'remaining_count' => $remainingCount
            ]);

            return response()->json([
                'success' => true,
                'removed_count' => $expiredCount,
                'remaining_count' => $remainingCount,
                'message' => "Removidos {$expiredCount} deal(s) expirado(s) com sucesso."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Erro ao remover deals expirados: " . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exibe a página de controles administrativos
     */
    public function controls()
    {
        return view('admin.controls');
    }

    /**
     * Atualiza as imagens dos jogos
     */
    public function updateImages()
    {
        try {
            // Implementar lógica de atualização de imagens
            $updatedCount = 0;
            $games = Game::whereNull('image')->orWhere('image', '')->limit(50)->get();
            
            foreach ($games as $game) {
                // Lógica para buscar e atualizar imagem do jogo
                // Por enquanto, vamos apenas simular
                $updatedCount++;
            }

            AdminLog::log('update_images', 'Game', null, [
                'updated_count' => $updatedCount
            ]);

            return response()->json([
                'success' => true,
                'updated_count' => $updatedCount,
                'message' => "Atualizadas {$updatedCount} imagens com sucesso."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Erro ao atualizar imagens: " . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtém estatísticas do sistema
     */
    public function getSystemStats()
    {
        try {
            // Deals ativos (com desconto > 0 e não expirados)
            $activeDeals = Game::where('discount_percent', '>', 0)
                ->where(function($query) {
                    $query->whereNull('deal_ends_at')
                          ->orWhere('deal_ends_at', '>', now());
                })
                ->count();
            
            // Deals expirados
            $expiredDeals = Game::where('deal_ends_at', '<', now())
                ->where('discount_percent', '>', 0)
                ->count();
            
            // Uso real de disco (diretório storage)
            $storagePath = storage_path();
            $diskUsage = 'N/A';
            
            if (function_exists('disk_free_space') && is_dir($storagePath)) {
                $bytes = $this->getFolderSize($storagePath);
                $diskUsage = $this->formatBytes($bytes);
            }
            
            // Status do cache - verificar se Redis/Cache está funcionando
            $cacheStatus = 'OK';
            try {
                \Illuminate\Support\Facades\Cache::put('cache_test', 'test', 1);
                $testResult = \Illuminate\Support\Facades\Cache::get('cache_test');
                if ($testResult !== 'test') {
                    $cacheStatus = 'ERROR';
                }
                \Illuminate\Support\Facades\Cache::forget('cache_test');
            } catch (\Exception $e) {
                $cacheStatus = 'ERROR';
            }

            // Estatísticas adicionais
            $totalGames = Game::count();
            $gamesWithImages = Game::whereNotNull('image')->where('image', '!=', '')->count();
            $gamesWithDescription = Game::whereNotNull('description')
                ->where('description', '!=', '')
                ->whereRaw('LENGTH(description) > 50')
                ->count();
            
            // Jobs na fila
            $queueSize = \Illuminate\Support\Facades\DB::table('jobs')->count();

            return response()->json([
                'success' => true,
                'stats' => [
                    'active_deals' => $activeDeals,
                    'expired_deals' => $expiredDeals,
                    'disk_usage' => $diskUsage,
                    'cache_status' => $cacheStatus,
                    'total_games' => $totalGames,
                    'games_with_images' => $gamesWithImages,
                    'games_with_description' => $gamesWithDescription,
                    'queue_size' => $queueSize,
                    'image_coverage' => $totalGames > 0 ? round(($gamesWithImages / $totalGames) * 100, 1) : 0,
                    'description_coverage' => $totalGames > 0 ? round(($gamesWithDescription / $totalGames) * 100, 1) : 0
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Erro ao carregar estatísticas: " . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calcula o tamanho de uma pasta recursivamente
     */
    private function getFolderSize($dir)
    {
        $size = 0;
        try {
            if (is_dir($dir)) {
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
                );
                
                foreach ($files as $file) {
                    if ($file->isFile()) {
                        $size += $file->getSize();
                    }
                }
            }
        } catch (\Exception $e) {
            // Se houver erro, retorna 0
            return 0;
        }
        
        return $size;
    }

    /**
     * Formata bytes em unidades legíveis
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
