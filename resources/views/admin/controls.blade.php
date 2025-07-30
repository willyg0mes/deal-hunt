@extends('admin.layout')

@section('title', 'Controles Administrativos')
@section('header', 'Controles do Sistema')
@section('description', 'Ferramentas administrativas para gerenciar deals, imagens e limpeza do sistema')

@section('content')
<!-- Sistema de Notificações Customizado -->
<div id="notification-container" class="fixed top-4 right-4 z-50 space-y-3 max-w-lg"></div>

<!-- Controles de Deals -->
<div class="bg-white p-6 rounded-xl shadow-sm border border-red-100 hover:shadow-lg transition-all duration-200 mb-8">
    <h3 class="text-lg font-semibold mb-6 text-gray-900 flex items-center">
        <svg class="w-6 h-6 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
        </svg>
        Gerenciamento de Deals
    </h3>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Busca Manual de Deals -->
        <div class="p-6 border border-red-100 rounded-lg bg-red-50/30 hover:bg-red-50/50 transition-colors">
            <h4 class="font-semibold mb-3 text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Busca Manual de Deals
            </h4>
            <p class="text-sm text-gray-600 mb-4">Execute uma busca manual na API CheapShark para novos deals. Buscas automáticas diárias limitadas a 100 deals.</p>
            <form id="fetchDealsForm" method="POST" action="{{ route('admin.fetch-deals') }}">
                @csrf
                <div class="flex items-center space-x-3 mb-3">
                    <label class="text-sm font-medium text-gray-700">Limite:</label>
                    <input type="number" name="limit" value="100" min="50" max="3000" 
                           class="w-24 px-3 py-2 border border-red-200 rounded-md text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow">
                        Buscar Deals
                    </button>
                </div>
                <p class="text-xs text-gray-500">
                    Limite: 50-3000 deals por busca manual
                </p>
            </form>
        </div>

        <!-- Coleta Completa de Deals -->
        <div class="p-6 border border-green-100 rounded-lg bg-green-50/30 hover:bg-green-50/50 transition-colors">
            <h4 class="font-semibold mb-3 text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                🚀 Coleta Completa de Deals
            </h4>
            <p class="text-sm text-gray-600 mb-4">Busca TODOS os deals disponíveis na CheapShark (sem limite) + dados detalhados da RAWG API.</p>
            <form id="collectDealsForm" method="POST" action="{{ route('admin.fetch-deals') }}">
                @csrf
                <input type="hidden" name="unlimited" value="true">
                <button type="submit" 
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow">
                    🎯 Buscar Todos os Deals
                </button>
            </form>
            <p class="text-xs text-gray-500 mt-2">
                ⚠️ Pode demorar vários minutos - busca ilimitada
            </p>
        </div>
    </div>
</div>

<!-- Ferramentas de Manutenção -->
<div class="bg-white p-6 rounded-xl shadow-sm border border-red-100 hover:shadow-lg transition-all duration-200 mb-8">
    <h3 class="text-lg font-semibold mb-6 text-gray-900 flex items-center">
        <svg class="w-6 h-6 mr-3 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        Ferramentas de Manutenção
    </h3>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Atualização de Imagens -->
        <div class="p-6 border border-blue-100 rounded-lg bg-blue-50/30 hover:bg-blue-50/50 transition-colors">
            <h4 class="font-semibold mb-3 text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Atualizar Imagens
            </h4>
            <p class="text-sm text-gray-600 mb-4">Atualizar imagens dos jogos com versões melhoradas da RAWG API.</p>
            <div class="space-y-3">
                <button onclick="updateImages()" 
                        class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow">
                    🔄 Atualizar Imagens
                </button>
                <p class="text-xs text-gray-500">
                    Busca imagens de melhor qualidade para os jogos existentes
                </p>
            </div>
        </div>

        <!-- Limpeza de Deals Expirados -->
        <div class="p-6 border border-orange-100 rounded-lg bg-orange-50/30 hover:bg-orange-50/50 transition-colors">
            <h4 class="font-semibold mb-3 text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                🧹 Limpeza de Deals Expirados
            </h4>
            <p class="text-sm text-gray-600 mb-4">Remove automaticamente deals que já expiraram baseado na data de expiração.</p>
            <div class="space-y-3">
                <div class="flex gap-2">
                    <button onclick="verifyExpiredDeals()" 
                            class="flex-1 px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow">
                        🔍 Verificar Expirados
                    </button>
                    <button onclick="removeExpiredDeals()" 
                            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow">
                        🗑️ Remover Expirados
                    </button>
                </div>
                <p class="text-xs text-gray-500">
                    🤖 Limpeza automática diária às 02:00
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Estatísticas do Sistema -->
<div class="bg-white p-6 rounded-xl shadow-sm border border-red-100 hover:shadow-lg transition-all duration-200">
    <h3 class="text-lg font-semibold mb-6 text-gray-900 flex items-center">
        <svg class="w-6 h-6 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        Status do Sistema
        <div class="ml-auto">
            <button onclick="loadSystemStats()" class="px-3 py-1 text-xs bg-purple-100 text-purple-700 rounded-full hover:bg-purple-200 transition-colors">
                🔄 Atualizar
            </button>
            <button onclick="testAPI()" class="ml-2 px-3 py-1 text-xs bg-red-100 text-red-700 rounded-full hover:bg-red-200 transition-colors">
                🧪 Testar API
            </button>
        </div>
    </h3>
    
    <!-- Primeira linha: Estatísticas principais -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Deals Ativos -->
        <div class="p-4 bg-gradient-to-r from-green-50 to-green-100 rounded-lg border border-green-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-green-800">Deals Ativos</p>
                    <p class="text-2xl font-bold text-green-900" id="active-deals">-</p>
                </div>
                <div class="p-2 bg-green-200 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Deals Expirados -->
        <div class="p-4 bg-gradient-to-r from-red-50 to-red-100 rounded-lg border border-red-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-red-800">Deals Expirados</p>
                    <p class="text-2xl font-bold text-red-900" id="expired-deals">-</p>
                </div>
                <div class="p-2 bg-red-200 rounded-lg">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Espaço em Disco -->
        <div class="p-4 bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg border border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-blue-800">Espaço Usado</p>
                    <p class="text-2xl font-bold text-blue-900" id="disk-usage">-</p>
                </div>
                <div class="p-2 bg-blue-200 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Cache Status -->
        <div class="p-4 bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg border border-purple-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-purple-800">Cache Status</p>
                    <p class="text-2xl font-bold text-purple-900" id="cache-status">-</p>
                </div>
                <div class="p-2 bg-purple-200 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Segunda linha: Estatísticas de conteúdo -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <!-- Total de Jogos -->
        <div class="p-4 bg-gradient-to-r from-indigo-50 to-indigo-100 rounded-lg border border-indigo-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-indigo-800">Total de Jogos</p>
                    <p class="text-xl font-bold text-indigo-900" id="total-games">-</p>
                </div>
                <div class="p-2 bg-indigo-200 rounded-lg">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Jobs na Fila -->
        <div class="p-4 bg-gradient-to-r from-yellow-50 to-yellow-100 rounded-lg border border-yellow-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-yellow-800">Jobs na Fila</p>
                    <p class="text-xl font-bold text-yellow-900" id="queue-size">-</p>
                </div>
                <div class="p-2 bg-yellow-200 rounded-lg">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Status Geral -->
        <div class="p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-800">Status Geral</p>
                    <p class="text-xl font-bold text-gray-900" id="system-health">🟢 OK</p>
                </div>
                <div class="p-2 bg-gray-200 rounded-lg">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Terceira linha: Cobertura de dados -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Cobertura de Imagens -->
        <div class="p-4 bg-gradient-to-r from-teal-50 to-teal-100 rounded-lg border border-teal-200">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-medium text-teal-800">Cobertura de Imagens</p>
                <span class="text-sm font-bold text-teal-900" id="image-coverage">-%</span>
            </div>
            <div class="w-full bg-teal-200 rounded-full h-2">
                <div class="bg-teal-600 h-2 rounded-full transition-all duration-300" id="image-progress-bar" style="width: 0%"></div>
            </div>
            <p class="text-xs text-teal-600 mt-1">
                <span id="games-with-images">-</span> jogos com imagens
            </p>
        </div>

        <!-- Cobertura de Descrições -->
        <div class="p-4 bg-gradient-to-r from-orange-50 to-orange-100 rounded-lg border border-orange-200">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-medium text-orange-800">Cobertura de Descrições</p>
                <span class="text-sm font-bold text-orange-900" id="description-coverage">-%</span>
            </div>
            <div class="w-full bg-orange-200 rounded-full h-2">
                <div class="bg-orange-600 h-2 rounded-full transition-all duration-300" id="description-progress-bar" style="width: 0%"></div>
            </div>
            <p class="text-xs text-orange-600 mt-1">
                <span id="games-with-description">-</span> jogos com descrições válidas
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Sistema de Notificações
function showNotification(type, title, message) {
    const container = document.getElementById('notification-container');
    const notificationId = 'notification-' + Date.now();
    
    const styles = {
        success: 'bg-green-50 border-green-200 text-green-800',
        error: 'bg-red-50 border-red-200 text-red-800',
        warning: 'bg-orange-50 border-orange-200 text-orange-800',
        info: 'bg-blue-50 border-blue-200 text-blue-800'
    };
    
    const style = styles[type] || styles.info;
    
    const notification = document.createElement('div');
    notification.id = notificationId;
    notification.className = `${style} border rounded-lg p-4 shadow-lg transform transition-all duration-300 ease-in-out`;
    notification.innerHTML = `
        <div class="flex justify-between items-start">
            <div class="flex-1">
                <h4 class="font-semibold">${title}</h4>
                <p class="text-sm mt-1">${message}</p>
            </div>
            <button onclick="removeNotification('${notificationId}')" class="ml-4 text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    `;
    
    container.appendChild(notification);
    
    // Auto remove após 5 segundos
    setTimeout(() => removeNotification(notificationId), 5000);
}

function removeNotification(id) {
    const notification = document.getElementById(id);
    if (notification) {
        notification.style.transform = 'translateX(100%)';
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    }
}

// Atualizar Imagens
function updateImages() {
    showNotification('info', 'Processando', 'Iniciando atualização de imagens...');
    
    fetch('/admin/update-images', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', 'Sucesso', data.message);
        } else {
            showNotification('error', 'Erro', data.message);
        }
    })
    .catch(error => {
        showNotification('error', 'Erro', 'Erro ao atualizar imagens: ' + error.message);
    });
}

// Verificar Deals Expirados
function verifyExpiredDeals() {
    showNotification('info', 'Verificando', 'Analisando deals expirados...');
    
    fetch('/admin/verify-expired-deals', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('info', 'Verificação Completa', `Encontrados ${data.expired_count} deals expirados`);
            document.getElementById('expired-deals').textContent = data.expired_count;
        } else {
            showNotification('error', 'Erro', data.message);
        }
    })
    .catch(error => {
        showNotification('error', 'Erro', 'Erro ao verificar deals: ' + error.message);
    });
}

// Remover Deals Expirados
function removeExpiredDeals() {
    if (!confirm('Tem certeza que deseja remover todos os deals expirados? Esta ação não pode ser desfeita.')) {
        return;
    }
    
    showNotification('warning', 'Removendo', 'Removendo deals expirados...');
    
    fetch('/admin/remove-expired-deals', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', 'Limpeza Completa', `${data.removed_count} deals expirados removidos`);
            loadSystemStats(); // Recarregar estatísticas
        } else {
            showNotification('error', 'Erro', data.message);
        }
    })
    .catch(error => {
        showNotification('error', 'Erro', 'Erro ao remover deals: ' + error.message);
    });
}

// Função de teste da API
function testAPI() {
    console.log('=== TESTE DA API ===');
    console.log('URL:', window.location.origin + '/admin/system-stats');
    
    showNotification('info', 'Teste', 'Testando conexão com API...');
    
    fetch('/admin/system-stats')
    .then(response => {
        console.log('Status da resposta:', response.status);
        console.log('Headers:', response.headers);
        console.log('OK:', response.ok);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        return response.text();
    })
    .then(text => {
        console.log('Resposta bruta:', text);
        
        try {
            const data = JSON.parse(text);
            console.log('JSON parseado:', data);
            
            if (data.success) {
                showNotification('success', 'Teste OK', 'API respondendo corretamente!');
                console.log('Deals ativos:', data.stats.active_deals);
                console.log('Total jogos:', data.stats.total_games);
                
                // Forçar carregamento das estatísticas
                loadSystemStats();
            } else {
                showNotification('error', 'Teste Falhou', 'API retornou erro: ' + data.message);
            }
        } catch (e) {
            console.error('Erro ao fazer parse do JSON:', e);
            showNotification('error', 'Teste Falhou', 'Resposta não é JSON válido');
        }
    })
    .catch(error => {
        console.error('Erro na requisição:', error);
        showNotification('error', 'Teste Falhou', 'Erro: ' + error.message);
    });
}

// Carregar Estatísticas do Sistema
function loadSystemStats() {
    console.log('🔄 Iniciando carregamento de estatísticas...');
    console.log('URL da requisição:', '/admin/system-stats');
    console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));
    
    fetch('/admin/system-stats', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(response => {
        console.log('✅ Resposta recebida:', {
            status: response.status,
            statusText: response.statusText,
            headers: Object.fromEntries(response.headers.entries()),
            url: response.url
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status} - ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('📊 Dados JSON recebidos:', data);
        
        if (data.success) {
            console.log('✅ Dados válidos, atualizando interface...');
            console.log('Stats:', data.stats);
            
            // Estatísticas principais
            const activeDealsEl = document.getElementById('active-deals');
            const expiredDealsEl = document.getElementById('expired-deals');
            const diskUsageEl = document.getElementById('disk-usage');
            const cacheStatusEl = document.getElementById('cache-status');
            
            console.log('Elementos encontrados:', {
                activeDealsEl: !!activeDealsEl,
                expiredDealsEl: !!expiredDealsEl,
                diskUsageEl: !!diskUsageEl,
                cacheStatusEl: !!cacheStatusEl
            });
            
            if (activeDealsEl) {
                activeDealsEl.textContent = data.stats.active_deals.toLocaleString();
                console.log('✅ Active deals atualizado:', data.stats.active_deals);
            }
            if (expiredDealsEl) {
                expiredDealsEl.textContent = data.stats.expired_deals.toLocaleString();
                console.log('✅ Expired deals atualizado:', data.stats.expired_deals);
            }
            if (diskUsageEl) {
                diskUsageEl.textContent = data.stats.disk_usage;
                console.log('✅ Disk usage atualizado:', data.stats.disk_usage);
            }
            if (cacheStatusEl) {
                cacheStatusEl.textContent = data.stats.cache_status;
                console.log('✅ Cache status atualizado:', data.stats.cache_status);
            }
            
            // Estatísticas de conteúdo
            const totalGamesEl = document.getElementById('total-games');
            const queueSizeEl = document.getElementById('queue-size');
            
            if (totalGamesEl) {
                totalGamesEl.textContent = data.stats.total_games.toLocaleString();
                console.log('✅ Total games atualizado:', data.stats.total_games);
            }
            if (queueSizeEl) {
                queueSizeEl.textContent = data.stats.queue_size.toLocaleString();
                console.log('✅ Queue size atualizado:', data.stats.queue_size);
            }
            
            // Cobertura de imagens
            const imageCoverageEl = document.getElementById('image-coverage');
            const gamesWithImagesEl = document.getElementById('games-with-images');
            const imageProgressBarEl = document.getElementById('image-progress-bar');
            
            if (imageCoverageEl) {
                imageCoverageEl.textContent = data.stats.image_coverage + '%';
                console.log('✅ Image coverage atualizado:', data.stats.image_coverage + '%');
            }
            if (gamesWithImagesEl) {
                gamesWithImagesEl.textContent = data.stats.games_with_images.toLocaleString();
                console.log('✅ Games with images atualizado:', data.stats.games_with_images);
            }
            if (imageProgressBarEl) {
                imageProgressBarEl.style.width = data.stats.image_coverage + '%';
                console.log('✅ Image progress bar atualizado:', data.stats.image_coverage + '%');
            }
            
            // Cobertura de descrições
            const descriptionCoverageEl = document.getElementById('description-coverage');
            const gamesWithDescriptionEl = document.getElementById('games-with-description');
            const descriptionProgressBarEl = document.getElementById('description-progress-bar');
            
            if (descriptionCoverageEl) {
                descriptionCoverageEl.textContent = data.stats.description_coverage + '%';
                console.log('✅ Description coverage atualizado:', data.stats.description_coverage + '%');
            }
            if (gamesWithDescriptionEl) {
                gamesWithDescriptionEl.textContent = data.stats.games_with_description.toLocaleString();
                console.log('✅ Games with description atualizado:', data.stats.games_with_description);
            }
            if (descriptionProgressBarEl) {
                descriptionProgressBarEl.style.width = data.stats.description_coverage + '%';
                console.log('✅ Description progress bar atualizado:', data.stats.description_coverage + '%');
            }
            
            // Status geral do sistema
            const systemHealth = document.getElementById('system-health');
            if (systemHealth) {
                if (data.stats.cache_status === 'OK' && data.stats.queue_size < 100) {
                    systemHealth.textContent = '🟢 OK';
                    systemHealth.className = 'text-xl font-bold text-green-900';
                } else if (data.stats.cache_status === 'ERROR' || data.stats.queue_size > 500) {
                    systemHealth.textContent = '🔴 PROBLEMA';
                    systemHealth.className = 'text-xl font-bold text-red-900';
                } else {
                    systemHealth.textContent = '🟡 ATENÇÃO';
                    systemHealth.className = 'text-xl font-bold text-yellow-900';
                }
                console.log('✅ System health atualizado:', systemHealth.textContent);
            }
            
            // Atualizar última atualização
            const now = new Date();
            const timeString = now.toLocaleTimeString('pt-BR');
            console.log(`🎉 Estatísticas atualizadas com sucesso às ${timeString}`);
            
            // Exibir notificação de sucesso
            showNotification('success', 'Estatísticas Atualizadas', `Dados atualizados às ${timeString}`);
            
        } else {
            console.error('❌ Erro na resposta da API:', data.message || 'Resposta inválida');
            showNotification('error', 'Erro', 'Erro ao carregar estatísticas: ' + (data.message || 'Resposta inválida'));
        }
    })
    .catch(error => {
        console.error('💥 Erro na requisição:', error);
        console.error('Stack trace:', error.stack);
        showNotification('error', 'Erro de Conexão', 'Erro ao conectar com API: ' + error.message);
        
        // Marcar sistema como com problema se API falhar
        const systemHealth = document.getElementById('system-health');
        if (systemHealth) {
            systemHealth.textContent = '🔴 API ERROR';
            systemHealth.className = 'text-xl font-bold text-red-900';
        }
    });
}

// Handlers para formulários
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM carregado, inicializando controles administrativos...');
    
    // Carregar estatísticas iniciais
    setTimeout(() => {
        console.log('Carregando estatísticas iniciais...');
        loadSystemStats();
    }, 500); // Aguardar 500ms para garantir que tudo está carregado
    
    // Handler para busca manual de deals
    document.getElementById('fetchDealsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const limit = formData.get('limit');
        
        showNotification('info', 'Buscando Deals', `Iniciando busca de ${limit} deals...`);
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('success', 'Busca Completa', data.message);
                loadSystemStats(); // Recarregar estatísticas
            } else {
                showNotification('error', 'Erro na Busca', data.message);
            }
        })
        .catch(error => {
            showNotification('error', 'Erro', 'Erro ao buscar deals: ' + error.message);
        });
    });
    
    // Handler para coleta completa de deals
    document.getElementById('collectDealsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!confirm('A coleta completa pode demorar vários minutos. Deseja continuar?')) {
            return;
        }
        
        const formData = new FormData(this);
        
        showNotification('warning', 'Coleta Completa Iniciada', 'Este processo pode demorar vários minutos...');
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('success', 'Coleta Completa', data.message);
                loadSystemStats(); // Recarregar estatísticas
            } else {
                showNotification('error', 'Erro na Coleta', data.message);
            }
        })
        .catch(error => {
            showNotification('error', 'Erro', 'Erro na coleta completa: ' + error.message);
        });
    });
    
    // Atualizar estatísticas a cada 30 segundos
    setInterval(loadSystemStats, 30000);
});
</script>
@endpush
