@extends('admin.layout')

@section('title', 'Painel de Traduções')
@section('header', 'Traduções')
@section('description', 'Gerenciamento e monitoramento de traduções automáticas')

@section('content')
<!-- Cards de Estatísticas -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Total de Jogos -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2h4a1 1 0 110 2h-1v14a2 2 0 01-2 2H6a2 2 0 01-2-2V6H3a1 1 0 110-2h4z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total de Jogos</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($totalGames) }}</p>
                </div>
            </div>
        </div>

        <!-- Jogos Traduzidos -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Traduzidos</p>
                    <p class="text-2xl font-semibold text-green-600 dark:text-green-400">{{ number_format($translatedGames) }}</p>
                </div>
            </div>
        </div>

        <!-- Jogos Não Traduzidos -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 dark:bg-red-900">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Não Traduzidos</p>
                    <p class="text-2xl font-semibold text-red-600 dark:text-red-400">{{ number_format($untranslatedGames) }}</p>
                </div>
            </div>
        </div>

        <!-- Taxa de Tradução -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Taxa de Tradução</p>
                    <p class="text-2xl font-semibold text-purple-600 dark:text-purple-400">
                        {{ $totalGames > 0 ? number_format(($translatedGames / $totalGames) * 100, 1) : 0 }}%
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Ações de Tradução -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
            ⚡ Ações de Tradução
        </h2>
        
        <div class="flex flex-wrap gap-4 mb-6">
            <!-- Detectar Novos Jogos -->
            <button 
                onclick="detectNewGames()"
                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                Detectar Novos Jogos
            </button>

            <!-- Traduzir em Lote -->
            <button 
                onclick="showBatchTranslationModal()"
                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Traduzir em Lote
            </button>

            <!-- Traduzir Todos Não Traduzidos -->
            <button 
                onclick="translateAllUntranslated()"
                class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                Traduzir Todos
            </button>
        </div>

        <!-- Progress Bar Container -->
        <div id="progressContainer" class="hidden flex-col mb-4">
            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-1">
                <span>Progresso da Tradução</span>
                <span id="progressText">0%</span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div id="progressBar" class="bg-blue-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
        </div>
    </div>

    <!-- Novos Jogos Não Traduzidos -->
    @if($newUntranslatedGames->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
            🆕 Novos Jogos Sem Tradução (Últimos 7 dias)
        </h2>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Jogo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Descrição</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Loja</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Adicionado</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($newUntranslatedGames as $game)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $game->title }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">ID: {{ $game->id }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 dark:text-white max-w-xs truncate">
                                {{ $game->description ? substr($game->description, 0, 100) . '...' : 'Sem descrição' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $game->store_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $game->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button 
                                onclick="translateSingleGame({{ $game->id }})"
                                class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 mr-3">
                                Traduzir
                            </button>
                            <a href="{{ route('games.show', $game) }}" 
                               class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300">
                                Ver
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Traduções Recentes -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
            📝 Traduções Recentes
        </h2>
        
        @if($recentTranslations->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Jogo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Método</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Confiança</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Traduzido em</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($recentTranslations as $translation)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $translation->game->title }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ substr($translation->translated_text, 0, 50) }}...
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $translation->translation_method === 'google_api' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 
                                   ($translation->translation_method === 'local' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                   'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200') }}">
                                {{ ucfirst($translation->translation_method) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    {{ number_format($translation->confidence_score * 100, 1) }}%
                                </div>
                                <div class="ml-2 w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="h-2 rounded-full {{ $translation->confidence_score >= 0.8 ? 'bg-green-500' : ($translation->confidence_score >= 0.6 ? 'bg-yellow-500' : 'bg-red-500') }}" 
                                         style="width: {{ $translation->confidence_score * 100 }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $translation->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button 
                                onclick="deleteTranslation({{ $translation->id }})"
                                class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 mr-3">
                                Excluir
                            </button>
                            <a href="{{ route('games.show', $translation->game) }}" 
                               class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300">
                                Ver
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-gray-500 dark:text-gray-400">Nenhuma tradução recente encontrada.</p>
        @endif
    </div>

    <!-- Estatísticas por Método -->
    @if($translationStats->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
            📊 Estatísticas por Método de Tradução
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($translationStats as $stat)
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <h3 class="font-medium text-gray-900 dark:text-white mb-2">
                    {{ ucfirst(str_replace('_', ' ', $stat->translation_method)) }}
                </h3>
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($stat->count) }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Confiança média: {{ number_format($stat->avg_confidence * 100, 1) }}%
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<!-- Modal para Tradução em Lote -->
<div id="batchTranslationModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Traduzir em Lote</h3>
            
            <form id="batchTranslationForm">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Número de jogos para traduzir:
                    </label>
                    <input type="number" id="batchLimit" min="1" max="1000" value="50" 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" id="forceRetranslate" 
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                            Força retradução (traduz jogos já traduzidos)
                        </span>
                    </label>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeBatchTranslationModal()" 
                            class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                        Iniciar Tradução
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ===== VARIÁVEIS GLOBAIS =====
let csrfToken;

// ===== FUNÇÕES GLOBAIS =====

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transition-all duration-300 ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        type === 'warning' ? 'bg-yellow-500 text-white' :
        'bg-blue-500 text-white'
    }`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 5000);
}

function detectNewGames() {
    fetch('/admin/translations/detect-new', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(`${data.count} novos jogos detectados para tradução`, 'success');
            
            if (data.count > 0) {
                setTimeout(() => location.reload(), 2000);
            }
        } else {
            showToast('Erro ao detectar novos jogos', 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast('Erro de conexão ao detectar novos jogos', 'error');
    });
}

function showBatchTranslationModal() {
    document.getElementById('batchTranslationModal').classList.remove('hidden');
}

function closeBatchTranslationModal() {
    document.getElementById('batchTranslationModal').classList.add('hidden');
}

function translateAllUntranslated() {
    // Criar modal de confirmação customizado
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4';
    modal.innerHTML = `
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
            <div class="flex items-center mb-4">
                <div class="bg-yellow-100 dark:bg-yellow-900 rounded-full p-2 mr-3">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Confirmar Tradução</h3>
            </div>
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                Isso irá traduzir automaticamente todos os jogos que ainda não possuem tradução. 
                Este processo pode levar alguns minutos. Deseja continuar?
            </p>
            <div class="flex justify-end gap-3">
                <button onclick="closeConfirmModal()" 
                        class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-colors">
                    Cancelar
                </button>
                <button onclick="executeTranslateAll()" 
                        class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg transition-colors">
                    Continuar
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Definir funções para o modal no escopo global
    window.closeConfirmModal = function() {
        modal.remove();
    };
    
    window.executeTranslateAll = function() {
        modal.remove();
        performTranslateAll();
    };
}

function performTranslateAll() {
    showProgress();
    
    // Configurar timeout de 10 minutos
    const timeoutId = setTimeout(() => {
        hideProgress();
        showToast('Operação cancelada por timeout (10 minutos)', 'warning');
    }, 600000); // 10 minutos
    
    fetch('/admin/translations/translate-all', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        clearTimeout(timeoutId);
        return response.json();
    })
    .then(data => {
        hideProgress();
        
        if (data.success) {
            showOperationResult(data.message, data.stats, 'success');
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 3000);
        } else {
            showOperationResult(data.message, null, 'error');
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        clearTimeout(timeoutId);
        hideProgress();
        console.error('Erro:', error);
        showToast('Erro de conexão durante a tradução', 'error');
    });
}

function translateSingleGame(gameId) {
    fetch('/admin/translations/translate-single', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            game_id: gameId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 2000);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast('Erro de conexão ao traduzir jogo', 'error');
    });
}

function showProgress() {
    const progressContainer = document.getElementById('progressContainer');
    if (progressContainer) {
        progressContainer.classList.remove('hidden');
        progressContainer.classList.add('flex');
        
        // Animação suave da barra de progresso
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        
        progressBar.style.width = '0%';
        progressText.textContent = 'Iniciando tradução...';
        
        // Simular progresso inicial
        setTimeout(() => {
            progressBar.style.width = '10%';
            progressText.textContent = 'Processando jogos...';
        }, 500);
    }
}

function hideProgress() {
    const progressContainer = document.getElementById('progressContainer');
    if (progressContainer) {
        progressContainer.classList.add('hidden');
        progressContainer.classList.remove('flex');
        
        // Reset da barra de progresso
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        
        progressBar.style.width = '0%';
        progressText.textContent = '';
    }
}

function updateProgress(percent) {
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    
    if (progressBar && progressText) {
        progressBar.style.width = percent + '%';
        progressText.textContent = `Progresso: ${percent}%`;
    }
}

function showOperationResult(message, stats, type) {
    const resultDiv = document.createElement('div');
    resultDiv.className = `fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-50 p-6 rounded-lg shadow-xl max-w-md w-full ${
        type === 'success' ? 'bg-green-100 border border-green-400' : 'bg-red-100 border border-red-400'
    }`;
    
    let content = `<h3 class="font-bold text-lg mb-2 ${type === 'success' ? 'text-green-800' : 'text-red-800'}">${message}</h3>`;
    
    if (stats) {
        content += `
            <div class="text-sm ${type === 'success' ? 'text-green-700' : 'text-red-700'}">
                <p>Jogos traduzidos: ${stats.translated || 0}</p>
                <p>Jogos ignorados: ${stats.ignored || 0}</p>
                <p>Erros: ${stats.errors || 0}</p>
                <p>Total processado: ${stats.total || stats.processed || 0}</p>
            </div>
        `;
    }
    
    resultDiv.innerHTML = content;
    document.body.appendChild(resultDiv);
    
    setTimeout(() => {
        resultDiv.remove();
    }, 5000);
}

function deleteTranslation(translationId) {
    if (!confirm('Tem certeza que deseja excluir esta tradução?')) {
        return;
    }
    
    fetch(`/admin/translations/${translationId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast('Erro de conexão ao excluir tradução', 'error');
    });
}

// ===== INICIALIZAÇÃO =====

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar CSRF token
    csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Event listener para o formulário de tradução em lote
    const batchForm = document.getElementById('batchTranslationForm');
    if (batchForm) {
        batchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const limit = document.getElementById('batchLimit').value;
            const force = document.getElementById('forceRetranslate').checked;
            
            // Fechar modal
            closeBatchTranslationModal();
            
            // Mostrar progress bar
            showProgress();
            
            // Executar tradução
            fetch('/admin/translations/batch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    limit: parseInt(limit),
                    force: force
                })
            })
            .then(response => response.json())
            .then(data => {
                hideProgress();
                
                if (data.success) {
                    showOperationResult(data.message, data.stats, 'success');
                    showToast(data.message, 'success');
                    setTimeout(() => location.reload(), 3000);
                } else {
                    showOperationResult(data.message, null, 'error');
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                hideProgress();
                console.error('Erro:', error);
                showToast('Erro de conexão durante a tradução', 'error');
            });
        });
    }
});
</script>

@endsection
