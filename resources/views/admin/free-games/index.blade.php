@extends('admin.layout')

@section('title', 'Gerenciar Jogos Gratuitos')
@section('header', 'Jogos Gratuitos Permanentes')
@section('description', 'Gerencie jogos que são gratuitos permanentes')

@section('content')
<div id="notification-container" class="fixed top-4 right-4 z-50 space-y-3 max-w-lg"></div>

<!-- Estatísticas -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
        <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total de Jogos</p>
                <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['total']) }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-green-200">
        <div class="flex items-center">
            <div class="p-2 bg-green-100 rounded-lg">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Gratuitos Permanentes</p>
                <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['free_permanent']) }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-yellow-200">
        <div class="flex items-center">
            <div class="p-2 bg-yellow-100 rounded-lg">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Com Preço Zero</p>
                <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['price_zero']) }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Ferramentas -->
<div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-8">
    <h3 class="text-lg font-semibold mb-4 text-gray-900 flex items-center">
        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        Ferramentas de Detecção
    </h3>
    
    <div class="flex flex-wrap gap-4">
                <button class="auto-detect-btn bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-md transition-colors">
            🤖 Auto-detectar Gratuitos
        </button>
        
        <button class="show-bulk-modal-btn bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition-colors">
            📦 Operações em Lote
        </button>
    </div>
</div>

<!-- Filtros -->
<div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-8">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
            <input type="text" id="search" name="search" value="{{ request('search') }}" 
                   placeholder="Nome do jogo..." 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        
        <div>
            <label for="filter" class="block text-sm font-medium text-gray-700 mb-2">Filtro</label>
            <select id="filter" name="filter" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Todos os jogos</option>
                <option value="free" {{ request('filter') == 'free' ? 'selected' : '' }}>Gratuitos permanentes</option>
                <option value="not_free" {{ request('filter') == 'not_free' ? 'selected' : '' }}>Não gratuitos</option>
                <option value="price_zero" {{ request('filter') == 'price_zero' ? 'selected' : '' }}>Com preço zero</option>
            </select>
        </div>
        
        <div class="md:col-span-2 flex items-end gap-2">
            <button type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-md transition-colors">
                Buscar
            </button>
            <a href="{{ route('admin.free-games.index') }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white font-medium px-6 py-2 rounded-md transition-colors">
                Limpar
            </a>
        </div>
    </form>
</div>

<!-- Lista de Jogos -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Jogos</h3>
        <p class="text-sm text-gray-600">{{ $games->total() }} jogos encontrados</p>
    </div>

    @if($games->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" id="select-all" class="rounded border-gray-300">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jogo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preços</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($games as $game)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" class="game-checkbox rounded border-gray-300" value="{{ $game->id }}">
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <img src="{{ $game->image }}" alt="{{ $game->title }}" 
                                         class="w-16 h-12 object-cover rounded mr-4">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $game->title }}</div>
                                        <div class="text-sm text-gray-500">{{ $game->store_name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    Normal: ${{ number_format($game->normal_price, 2) }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    Promo: ${{ number_format($game->sale_price, 2) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($game->is_free_permanently)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        ✅ Gratuito Permanente
                                    </span>
                                    @if($game->free_since)
                                        <div class="text-xs text-gray-500 mt-1">
                                            Desde {{ $game->free_since->format('d/m/Y') }}
                                        </div>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Não gratuito
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                @if($game->is_free_permanently)
                                    <button data-game-id="{{ $game->id }}" 
                                            class="remove-free-btn text-red-600 hover:text-red-900">
                                        Remover
                                    </button>
                                @else
                                    <button data-game-id="{{ $game->id }}" 
                                            class="mark-free-btn text-green-600 hover:text-green-900">
                                        Marcar Gratuito
                                    </button>
                                @endif
                                <a href="{{ route('games.show', $game) }}" 
                                   class="text-blue-600 hover:text-blue-900">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $games->appends(request()->query())->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum jogo encontrado</h3>
            <p class="mt-1 text-sm text-gray-500">
                @if(request()->hasAny(['search', 'filter']))
                    Tente ajustar os filtros ou fazer uma nova busca.
                @else
                    Comece adicionando alguns jogos gratuitos.
                @endif
            </p>
        </div>
    @endif
</div>

<!-- Modal para marcar como gratuito -->
<div id="free-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <h3 class="text-lg font-semibold mb-4">Marcar como Gratuito Permanente</h3>
            <form id="free-form">
                <div class="mb-4">
                    <label for="free_description" class="block text-sm font-medium text-gray-700 mb-2">
                        Descrição (opcional)
                    </label>
                    <textarea id="free_description" name="free_description" rows="3" 
                              placeholder="Ex: Promoção permanente da Epic Games..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" class="close-free-modal-btn bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">
                        Marcar como Gratuito
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para ação em massa -->
<div id="bulk-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <h3 class="text-lg font-semibold mb-4">Ação em Massa</h3>
            <form id="bulk-form">
                <div class="mb-4">
                    <label for="bulk_free_description" class="block text-sm font-medium text-gray-700 mb-2">
                        Descrição para todos (opcional)
                    </label>
                    <textarea id="bulk_free_description" name="free_description" rows="3" 
                              placeholder="Ex: Lote de jogos marcados como gratuitos..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" class="close-bulk-modal-btn bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">
                        Marcar Selecionados
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentGameId = null;

// Sistema de notificações
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
            <button data-notification-id="${notificationId}" class="remove-notification-btn ml-4 text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    `;
    
    container.appendChild(notification);
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

// Marcar jogo como gratuito
function markAsFree(gameId) {
    currentGameId = gameId;
    document.getElementById('free-modal').classList.remove('hidden');
}

function closeFreeModal() {
    document.getElementById('free-modal').classList.add('hidden');
    document.getElementById('free-form').reset();
    currentGameId = null;
}

// Remover jogo dos gratuitos
function removeFree(gameId) {
    if (!confirm('Tem certeza que deseja remover este jogo dos gratuitos permanentes?')) {
        return;
    }
    
    fetch(`/admin/free-games/${gameId}/remove-free`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', 'Removido', data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('error', 'Erro', data.message || 'Erro ao remover jogo');
        }
    })
    .catch(error => {
        showNotification('error', 'Erro', 'Erro na requisição: ' + error.message);
    });
}

function showBulkModal() {
    const selected = document.querySelectorAll('.game-checkbox:checked');
    if (selected.length === 0) {
        showNotification('warning', 'Atenção', 'Selecione pelo menos um jogo primeiro');
        return;
    }
    document.getElementById('bulk-modal').classList.remove('hidden');
}

function closeBulkModal() {
    document.getElementById('bulk-modal').classList.add('hidden');
    document.getElementById('bulk-form').reset();
}

// Detecção automática
function autoDetectFreeGames() {
    showNotification('info', 'Detectando', 'Procurando jogos com preço zero...');
    
    fetch('/admin/free-games/auto-detect', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const count = data.candidates.length;
            if (count > 0) {
                showNotification('success', 'Encontrados', `${count} candidatos a jogos gratuitos encontrados! Verifique a lista.`);
                console.log('Candidatos encontrados:', data.candidates);
            } else {
                showNotification('info', 'Nenhum candidato', 'Nenhum novo candidato a jogo gratuito foi encontrado.');
            }
        } else {
            showNotification('error', 'Erro', 'Erro na detecção automática');
        }
    })
    .catch(error => {
        showNotification('error', 'Erro', 'Erro na requisição: ' + error.message);
    });
}

// Event Listeners para evitar onclick inline
document.addEventListener('DOMContentLoaded', function() {
    // Botões de marcar como gratuito
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('mark-free-btn')) {
            const gameId = e.target.dataset.gameId;
            markAsFree(gameId);
        }
        
        // Botões de remover gratuito
        if (e.target.classList.contains('remove-free-btn')) {
            const gameId = e.target.dataset.gameId;
            removeFree(gameId);
        }
        
        // Botão auto-detectar
        if (e.target.classList.contains('auto-detect-btn')) {
            autoDetectFreeGames();
        }
        
        // Botão bulk modal
        if (e.target.classList.contains('show-bulk-modal-btn')) {
            showBulkModal();
        }
        
        // Botões de fechar modais
        if (e.target.classList.contains('close-free-modal-btn')) {
            closeFreeModal();
        }
        
        if (e.target.classList.contains('close-bulk-modal-btn')) {
            closeBulkModal();
        }
        
        // Botões de remover notificação
        if (e.target.classList.contains('remove-notification-btn')) {
            const notificationId = e.target.dataset.notificationId;
            removeNotification(notificationId);
        }
    });
    
    // Select all checkbox
    const selectAllCheckbox = document.getElementById('select-all');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.game-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }
    
    // Form de marcar como gratuito
    const freeForm = document.getElementById('free-form');
    if (freeForm) {
        freeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch(`/admin/free-games/${currentGameId}/mark-free`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('success', 'Sucesso', data.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification('error', 'Erro', data.message || 'Erro ao marcar jogo como gratuito');
                }
                closeFreeModal();
            })
            .catch(error => {
                showNotification('error', 'Erro', 'Erro na requisição: ' + error.message);
                closeFreeModal();
            });
        });
    }
    
    // Form de bulk operations
    const bulkForm = document.getElementById('bulk-form');
    if (bulkForm) {
        bulkForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const selected = document.querySelectorAll('.game-checkbox:checked');
            const gameIds = Array.from(selected).map(cb => cb.value);
            
            const formData = new FormData(this);
            formData.append('game_ids', JSON.stringify(gameIds));
            
            fetch('/admin/free-games/bulk-mark-free', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('success', 'Sucesso', data.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification('error', 'Erro', data.message || 'Erro na ação em massa');
                }
                closeBulkModal();
            })
            .catch(error => {
                showNotification('error', 'Erro', 'Erro na requisição: ' + error.message);
                closeBulkModal();
            });
        });
    }
});
</script>
@endpush
