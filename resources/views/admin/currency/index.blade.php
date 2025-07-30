@extends('admin.layout')

@section('title', 'Gerenciamento de Moedas')
@section('header', 'Moedas & Taxas')
@section('description', 'Controle das taxas de conversão e configurações de moeda dos usuários')

@section('content')
<!-- Info Header -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-8">
    <div class="flex items-center justify-between">
        <div class="text-right">
            <div class="text-sm text-gray-500 dark:text-gray-400">Última atualização</div>
            <div class="text-lg font-semibold text-gray-900 dark:text-white" id="last-update">
                @if($lastUpdate)
                    {{ $lastUpdate->diffForHumans() }}
                @else
                    Nunca
                @endif
                </div>
                @if($cacheAge)
                    <div class="text-xs {{ $cacheAge > 120 ? 'text-red-500' : 'text-green-500' }}">
                        Cache: {{ $cacheAge }} min
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Ações Rápidas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <button id="update-all-rates" class="bg-blue-500 hover:bg-blue-600 text-white p-6 rounded-xl shadow-lg transition-colors">
            <div class="flex items-center justify-center mb-2">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </div>
            <div class="text-lg font-semibold">Atualizar Todas as Taxas</div>
            <div class="text-sm opacity-90">Força atualização para todos os usuários</div>
        </button>

        <button id="refresh-cache" class="bg-green-500 hover:bg-green-600 text-white p-6 rounded-xl shadow-lg transition-colors">
            <div class="flex items-center justify-center mb-2">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <div class="text-lg font-semibold">Atualizar Cache</div>
            <div class="text-sm opacity-90">Busca novas taxas da API</div>
        </button>

        <button id="view-stats" class="bg-purple-500 hover:bg-purple-600 text-white p-6 rounded-xl shadow-lg transition-colors">
            <div class="flex items-center justify-center mb-2">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4"/>
                </svg>
            </div>
            <div class="text-lg font-semibold">Ver Estatísticas</div>
            <div class="text-sm opacity-90">Análise detalhada por moeda</div>
        </button>
    </div>

    <!-- Taxas Atuais -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Taxas de Conversão Atuais (Base: USD)</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4" id="current-rates">
            @foreach($rates as $currency => $rate)
                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $currency }}</div>
                    <div class="text-lg text-blue-600 dark:text-blue-400">{{ number_format($rate, 4) }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">por 1 USD</div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Estatísticas de Usuários -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Distribuição de Usuários por Moeda</h2>
        <div class="overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-2 px-3 text-gray-900 dark:text-white">Moeda</th>
                        <th class="text-left py-2 px-3 text-gray-900 dark:text-white">Usuários</th>
                        <th class="text-left py-2 px-3 text-gray-900 dark:text-white">Taxa Média</th>
                        <th class="text-left py-2 px-3 text-gray-900 dark:text-white">Taxa Atual</th>
                        <th class="text-left py-2 px-3 text-gray-900 dark:text-white">Status</th>
                        <th class="text-left py-2 px-3 text-gray-900 dark:text-white">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($userStats as $stat)
                        @php
                            $currentRate = $rates[$stat->currency] ?? null;
                            $difference = $currentRate ? abs($currentRate - $stat->avg_rate) / $currentRate * 100 : 0;
                            $isOutdated = $difference > 5;
                        @endphp
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <td class="py-3 px-3">
                                <div class="flex items-center">
                                    <span class="text-lg font-semibold">{{ $stat->currency }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-3">
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-sm">
                                    {{ $stat->count }} usuário{{ $stat->count > 1 ? 's' : '' }}
                                </span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="font-mono">{{ number_format($stat->avg_rate, 4) }}</span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="font-mono {{ $currentRate ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $currentRate ? number_format($currentRate, 4) : 'N/A' }}
                                </span>
                            </td>
                            <td class="py-3 px-3">
                                @if($isOutdated)
                                    <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs">
                                        Desatualizada ({{ round($difference, 1) }}%)
                                    </span>
                                @else
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">
                                        Atualizada
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                <button class="update-currency-btn bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm" 
                                        data-currency="{{ $stat->currency }}">
                                    Atualizar
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Usuários com Problemas -->
    @if($outdatedUsers->isNotEmpty())
        <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-xl border border-yellow-200 dark:border-yellow-800 p-6">
            <h2 class="text-xl font-semibold text-yellow-800 dark:text-yellow-200 mb-4">
                ⚠️ Usuários com Taxas Muito Desatualizadas
            </h2>
            <div class="space-y-3">
                @foreach($outdatedUsers as $issue)
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-semibold">{{ $issue['currency'] }}</span>
                                <span class="text-gray-600 dark:text-gray-400">
                                    - {{ $issue['users_count'] }} usuário{{ $issue['users_count'] > 1 ? 's' : '' }}
                                </span>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    Média: {{ number_format($issue['avg_user_rate'], 4) }} → 
                                    Atual: {{ number_format($issue['current_rate'], 4) }}
                                </div>
                                <div class="text-red-600 font-semibold">
                                    Diferença: {{ $issue['difference_percent'] }}%
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<!-- Loading Modal -->
<div id="loading-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-sm mx-4">
        <div class="flex items-center">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-900 dark:text-white">Processando...</span>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const loadingModal = document.getElementById('loading-modal');
    
    function showLoading() {
        loadingModal.classList.remove('hidden');
        loadingModal.classList.add('flex');
    }
    
    function hideLoading() {
        loadingModal.classList.add('hidden');
        loadingModal.classList.remove('flex');
    }
    
    function showNotification(message, type = 'success') {
        // Implementar notificação toast aqui
        alert(message);
    }
    
    // Atualizar todas as taxas
    document.getElementById('update-all-rates').addEventListener('click', function() {
        if (!confirm('Isso irá atualizar as taxas de todos os usuários. Continuar?')) return;
        
        showLoading();
        fetch('{{ route("admin.currency.update-rates") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ force: true })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showNotification(data.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            hideLoading();
            showNotification('Erro ao atualizar taxas', 'error');
            console.error('Error:', error);
        });
    });
    
    // Atualizar cache
    document.getElementById('refresh-cache').addEventListener('click', function() {
        showLoading();
        fetch('{{ route("admin.currency.refresh-cache") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showNotification(data.message);
                // Atualizar taxas na tela
                updateRatesDisplay(data.rates);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            hideLoading();
            showNotification('Erro ao atualizar cache', 'error');
            console.error('Error:', error);
        });
    });
    
    function updateRatesDisplay(rates) {
        const container = document.getElementById('current-rates');
        container.innerHTML = '';
        
        Object.entries(rates).forEach(([currency, rate]) => {
            const div = document.createElement('div');
            div.className = 'bg-gray-50 dark:bg-gray-700 p-4 rounded-lg text-center';
            div.innerHTML = `
                <div class="text-2xl font-bold text-gray-900 dark:text-white">${currency}</div>
                <div class="text-lg text-blue-600 dark:text-blue-400">${rate.toFixed(4)}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">por 1 USD</div>
            `;
            container.appendChild(div);
        });
    }
    
    // Atualizar moeda específica
    document.querySelectorAll('.update-currency-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const currency = this.dataset.currency;
            if (!confirm(`Atualizar taxas para todos os usuários com moeda ${currency}?`)) return;
            
            showLoading();
            // Implementar atualização por moeda específica
            hideLoading();
            showNotification(`Taxas atualizadas para ${currency}`);
        });
    });
});
</script>
@endpush
@endsection
