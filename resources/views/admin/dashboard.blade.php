@extends('admin.layout')

@section('title', 'Dashboard Administrativo')
@section('header', 'Dashboard')
@section('description', 'Visão geral do sistema e estatísticas importantes')

@section('content')
<!-- Sistema de Notificações Customizado -->
<div id="notification-container" class="fixed top-4 right-4 z-50 space-y-3 max-w-lg"></div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Card: Total de Jogos -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-red-100 hover:shadow-lg hover:border-red-200 transition-all duration-200">
        <div class="flex items-center">
            <div class="p-3 bg-gradient-to-r from-red-100 to-red-200 rounded-xl">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total de Jogos</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalGames) }}</p>
            </div>
        </div>
        <div class="mt-4 flex items-center">
            <div class="flex items-center text-red-600">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
                <span class="text-sm font-medium">+{{ $recentGames }}</span>
            </div>
            <span class="text-sm text-gray-500 ml-1">adicionados hoje</span>
        </div>
    </div>

    <!-- Card: Total de Usuários -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-red-100 hover:shadow-lg hover:border-red-200 transition-all duration-200">
        <div class="flex items-center">
            <div class="p-3 bg-gradient-to-r from-red-100 to-red-200 rounded-xl">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total de Usuários</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalUsers) }}</p>
            </div>
        </div>
        <div class="mt-4 flex items-center">
            <div class="flex items-center text-red-600">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                <span class="text-sm font-medium">{{ $totalAdmins }}</span>
            </div>
            <span class="text-sm text-gray-500 ml-1">administradores</span>
        </div>
    </div>

    <!-- Card: API Calls -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-red-100 hover:shadow-lg hover:border-red-200 transition-all duration-200">
        <div class="flex items-center">
            <div class="p-3 bg-gradient-to-r from-red-100 to-red-200 rounded-xl">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Chamadas API (7d)</p>
                <p class="text-2xl font-bold text-gray-900">{{ $apiCalls->sum('value') }}</p>
            </div>
        </div>
    </div>

    <!-- Card: Buscas -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-red-100 hover:shadow-lg hover:border-red-200 transition-all duration-200">
        <div class="flex items-center">
            <div class="p-3 bg-gradient-to-r from-red-100 to-red-200 rounded-xl">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Buscas (7d)</p>
                <p class="text-2xl font-bold text-gray-900">{{ $gameSearches->sum('value') }}</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Estatísticas de Visualizações -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-red-100 hover:shadow-lg transition-all duration-200">
        <h3 class="text-lg font-semibold mb-4 text-gray-900 flex items-center">
            <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Visualizações (Últimos 7 Dias)
        </h3>
        <div class="relative h-64">
            <canvas id="pageViewsChart"></canvas>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Top Deals -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-red-100 hover:shadow-lg transition-all duration-200">
        <h3 class="text-lg font-semibold mb-4 text-gray-900 flex items-center">
            <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
            </svg>
            Maiores Descontos
        </h3>
        <div class="space-y-3">
            @forelse($topDeals as $deal)
                <div class="flex items-center justify-between p-3 bg-red-50/30 hover:bg-red-50/50 rounded-lg border border-red-100 transition-colors">
                    <div class="flex items-center">
                        <img src="{{ $deal->image }}" alt="{{ $deal->title }}" 
                             class="w-12 h-12 object-cover rounded-lg border border-red-200">
                        <div class="ml-3">
                            <p class="font-medium text-sm text-gray-900">{{ Str::limit($deal->title, 30) }}</p>
                            <p class="text-xs text-gray-600">{{ $deal->store_name }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="bg-red-600 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-sm">
                            -{{ $deal->discount_percent }}%
                        </span>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center py-8">Nenhum deal encontrado.</p>
            @endforelse
        </div>
    </div>

    <!-- Logs Recentes -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-red-100 hover:shadow-lg transition-all duration-200">
        <h3 class="text-lg font-semibold mb-4 text-gray-900 flex items-center">
            <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Atividades Recentes
        </h3>
        <div class="space-y-3">
            @forelse($recentLogs as $log)
                <div class="flex items-center justify-between p-3 bg-red-50/30 hover:bg-red-50/50 rounded-lg border border-red-100 transition-colors">
                    <div>
                        <p class="font-medium text-sm text-gray-900">{{ $log->action }}</p>
                        <p class="text-xs text-gray-600">
                            {{ $log->user?->name ?? 'Sistema' }} • {{ $log->created_at->diffForHumans() }}
                        </p>
                    </div>
                    @if($log->entity_type)
                        <span class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded-full font-medium">
                            {{ $log->entity_type }}
                        </span>
                    @endif
                </div>
            @empty
                <p class="text-gray-500 text-center py-8">Nenhuma atividade recente.</p>
            @endforelse
        </div>
        <div class="mt-4 text-center">
            <a href="{{ route('admin.logs') }}" class="inline-flex items-center text-red-600 hover:text-red-700 text-sm font-medium transition-colors">
                Ver todos os logs 
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>
    </div>
</div>

<script>
// Sistema de Notificações Customizado
function showNotification(type, title, message, details = null) {
    const container = document.getElementById('notification-container');
    const notificationId = 'notification-' + Date.now();
    
    // Cores e ícones baseados no tipo
    const styles = {
        success: {
            bg: 'bg-green-50 border-green-200',
            titleColor: 'text-green-800',
            textColor: 'text-green-700',
            icon: `<svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>`
        },
        error: {
            bg: 'bg-red-50 border-red-200',
            titleColor: 'text-red-800',
            textColor: 'text-red-700',
            icon: `<svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>`
        },
        warning: {
            bg: 'bg-orange-50 border-orange-200',
            titleColor: 'text-orange-800',
            textColor: 'text-orange-700',
            icon: `<svg class="w-5 h-5 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>`
        },
        info: {
            bg: 'bg-blue-50 border-blue-200',
            titleColor: 'text-blue-800',
            textColor: 'text-blue-700',
            icon: `<svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
            </svg>`
        }
    };
    
    const style = styles[type] || styles.info;
    
    // Criar detalhes se fornecidos
    let detailsHtml = '';
    if (details) {
        if (typeof details === 'object') {
            detailsHtml = `<div class="mt-3 text-sm ${style.textColor} bg-white/30 rounded-lg p-4 border border-white/20 space-y-2">`;
            for (const [key, value] of Object.entries(details)) {
                detailsHtml += `<div class="flex justify-between items-center py-2 px-1 border-b border-white/10 last:border-b-0">
                    <span class="font-medium text-left min-w-0 flex-1 pr-4">${key}:</span> 
                    <span class="font-bold text-right min-w-0 flex-1 text-right">${value}</span>
                </div>`;
            }
            detailsHtml += `</div>`;
        } else {
            detailsHtml = `<div class="mt-3 text-sm ${style.textColor} bg-white/30 rounded-lg p-3 border border-white/20 leading-relaxed">${details}</div>`;
        }
    }
    
    const notification = document.createElement('div');
    notification.id = notificationId;
    notification.className = `min-w-96 max-w-lg w-full ${style.bg} border rounded-lg shadow-lg transform transition-all duration-300 translate-x-full opacity-0`;
    notification.innerHTML = `
        <div class="p-5">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    ${style.icon}
                </div>
                <div class="ml-4 flex-1 min-w-0">
                    <p class="text-sm font-medium ${style.titleColor} mb-2">${title}</p>
                    <p class="text-sm ${style.textColor} mb-3 leading-relaxed">${message}</p>
                    ${detailsHtml}
                </div>
                <div class="ml-4 flex-shrink-0">
                    <button onclick="removeNotification('${notificationId}')" class="${style.textColor} hover:${style.titleColor} focus:outline-none transition-colors p-1 rounded">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    container.appendChild(notification);
    
    // Animar entrada
    setTimeout(() => {
        notification.classList.remove('translate-x-full', 'opacity-0');
        notification.classList.add('translate-x-0', 'opacity-100');
    }, 10);
    
    // Auto remover após 8 segundos
    setTimeout(() => {
        removeNotification(notificationId);
    }, 8000);
}

function removeNotification(notificationId) {
    const notification = document.getElementById(notificationId);
    if (notification) {
        notification.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }
}

// Sistema de Modal de Confirmação Customizado
function showConfirmModal(title, message, type = 'info', onConfirm = null, onCancel = null) {
    // Remover modal existente se houver
    const existingModal = document.getElementById('confirm-modal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Cores baseadas no tipo
    const styles = {
        info: {
            bg: 'bg-blue-50',
            border: 'border-blue-200',
            titleColor: 'text-blue-800',
            textColor: 'text-blue-700',
            confirmBtn: 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',
            icon: `<svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>`
        },
        warning: {
            bg: 'bg-orange-50',
            border: 'border-orange-200',
            titleColor: 'text-orange-800',
            textColor: 'text-orange-700',
            confirmBtn: 'bg-orange-600 hover:bg-orange-700 focus:ring-orange-500',
            icon: `<svg class="w-6 h-6 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>`
        },
        error: {
            bg: 'bg-red-50',
            border: 'border-red-200',
            titleColor: 'text-red-800',
            textColor: 'text-red-700',
            confirmBtn: 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
            icon: `<svg class="w-6 h-6 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>`
        }
    };
    
    const style = styles[type] || styles.info;
    
    const modal = document.createElement('div');
    modal.id = 'confirm-modal';
    modal.className = 'fixed inset-0 z-50 overflow-y-auto';
    modal.innerHTML = `
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeConfirmModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom ${style.bg} ${style.border} rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                            ${style.icon}
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium ${style.titleColor}">${title}</h3>
                            <div class="mt-2">
                                <p class="text-sm ${style.textColor}">${message}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="confirmAction()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 ${style.confirmBtn} text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">
                        Confirmar
                    </button>
                    <button type="button" onclick="closeConfirmModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Funções globais para o modal
    window.confirmAction = function() {
        closeConfirmModal();
        if (onConfirm) onConfirm();
    };
    
    window.closeConfirmModal = function() {
        modal.remove();
        if (onCancel) onCancel();
    };
}

// Gráfico de visualizações
const ctx = document.getElementById('pageViewsChart').getContext('2d');
const pageViewsData = {!! json_encode($pageViews->pluck('date')->map(fn($date) => $date->format('d/m'))) !!};
const pageViewsValues = {!! json_encode($pageViews->pluck('value')) !!};

const chart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: pageViewsData,
        datasets: [{
            label: 'Visualizações',
            data: pageViewsValues,
            borderColor: 'rgb(220, 38, 38)',
            backgroundColor: function(context) {
                const chart = context.chart;
                const {ctx, chartArea} = chart;
                if (!chartArea) {
                    return null;
                }
                const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                gradient.addColorStop(0, 'rgba(220, 38, 38, 0.3)');
                gradient.addColorStop(0.5, 'rgba(220, 38, 38, 0.1)');
                gradient.addColorStop(1, 'rgba(220, 38, 38, 0.02)');
                return gradient;
            },
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: 'rgb(220, 38, 38)',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 6,
            pointHoverRadius: 8,
            pointHoverBackgroundColor: 'rgb(185, 28, 28)',
            pointHoverBorderColor: '#ffffff',
            pointHoverBorderWidth: 3
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            intersect: false,
            mode: 'index'
        },
        scales: {
            x: {
                grid: {
                    color: 'rgba(220, 38, 38, 0.1)',
                    borderColor: 'rgba(220, 38, 38, 0.2)'
                },
                ticks: {
                    color: 'rgb(107, 114, 128)',
                    font: {
                        weight: '500'
                    }
                }
            },
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(220, 38, 38, 0.1)',
                    borderColor: 'rgba(220, 38, 38, 0.2)'
                },
                ticks: {
                    color: 'rgb(107, 114, 128)',
                    font: {
                        weight: '500'
                    }
                }
            }
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: 'rgba(31, 41, 55, 0.9)',
                titleColor: '#ffffff',
                bodyColor: '#ffffff',
                borderColor: 'rgb(220, 38, 38)',
                borderWidth: 1,
                cornerRadius: 8,
                padding: 12,
                displayColors: false
            }
        },
        elements: {
            line: {
                capBezierPoints: false
            }
        }
    }
});

// Função para buscar deals (limitado)
document.getElementById('fetchDealsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const button = form.querySelector('button');
    const originalText = button.textContent;
    const limitInput = form.querySelector('input[name="limit"]');
    const limit = parseInt(limitInput.value);
    
    // Calcular número estimado de requisições
    const estimatedPages = Math.ceil(limit / 60);
    
    showConfirmModal(
        'Busca Limitada de Deals',
        `Esta operação fará aproximadamente ${estimatedPages} requisição(ões) para buscar ${limit} deals. Deseja continuar?`,
        'info',
        () => {
            button.disabled = true;
            button.innerHTML = `
                <svg class="animate-spin h-4 w-4 mr-2 inline" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Fazendo ${estimatedPages} requisições...
            `;
            
            showNotification('info', 'Busca Iniciada', `Fazendo ${estimatedPages} requisição(ões) para buscar ${limit} deals...`);
            
            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const details = {
                        'Requisições feitas': data.pages || 'N/A',
                        'Deals coletados': data.processed || 'N/A',
                        'Jogos atualizados': data.updated || 'N/A',
                        'Total de jogos': data.total_games || 'N/A'
                    };
                    
                    showNotification(
                        'success',
                        'Busca Limitada Concluída!',
                        'Todos os deals foram coletados com sucesso.',
                        details
                    );
                    
                    setTimeout(() => {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            window.location.reload();
                        }
                    }, 3000);
                } else {
                    showNotification('error', 'Erro na Busca', data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showNotification('error', 'Erro de Conexão', 'Erro ao processar solicitação. Verifique o console para mais detalhes.');
            })
            .finally(() => {
                button.disabled = false;
                button.textContent = originalText;
            });
        }
    );
});

// Função para coleta completa de deals (ilimitado)
document.getElementById('collectDealsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    showConfirmModal(
        'Coleta Completa de Deals',
        'Esta operação pode demorar vários minutos e buscar milhares de deals. Deseja continuar?',
        'warning',
        () => {
            const form = this;
            const button = form.querySelector('button');
            const originalText = button.textContent;
            
            button.disabled = true;
            button.innerHTML = `
                <svg class="animate-spin h-4 w-4 mr-2 inline" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Coletando todos os deals...
            `;
            
            showNotification('info', 'Coleta Iniciada', 'Aguarde enquanto coletamos todos os deals disponíveis...');
            
            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const details = {
                        'Páginas processadas': data.pages || 'N/A',
                        'Deals coletados': data.deals || 'N/A',
                        'Jogos novos': data.new_games || 'N/A',
                        'Jogos atualizados': data.updated_games || 'N/A',
                        'Total de jogos': data.total_games || 'N/A'
                    };
                    
                    showNotification(
                        'success',
                        'Coleta Finalizada!',
                        'Todos os deals foram coletados com sucesso.',
                        details
                    );
                    
                    setTimeout(() => {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            window.location.reload();
                        }
                    }, 3000);
                } else {
                    showNotification('error', 'Erro na Coleta', data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showNotification('error', 'Erro de Conexão', 'Erro ao processar solicitação. Verifique o console para mais detalhes.');
            })
            .finally(() => {
                button.disabled = false;
                button.textContent = originalText;
            });
        }
    );
});

// Função para verificar deals expirados (dry-run)
function verifyExpiredDeals() {
    showConfirmModal(
        'Verificar Deals Expirados',
        'Esta operação irá verificar quantos deals estão expirados sem remover nada. Deseja continuar?',
        'info',
        () => {
            fetch('{{ route("admin.cleanup.verify") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const details = {
                        'Deals expirados': data.expired_count || 0,
                        'Total de deals': data.total_count || 0
                    };
                    
                    showNotification(
                        data.expired_count > 0 ? 'warning' : 'success',
                        'Verificação Concluída',
                        data.expired_count > 0 
                            ? `Encontrados ${data.expired_count} deal(s) expirado(s) que podem ser removidos.`
                            : 'Não há deals expirados no momento.',
                        details
                    );
                } else {
                    showNotification('error', 'Erro na Verificação', data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showNotification('error', 'Erro de Conexão', 'Erro ao verificar deals expirados.');
            });
        }
    );
}

// Função para remover deals expirados
function removeExpiredDeals() {
    showConfirmModal(
        'Remover Deals Expirados',
        'ATENÇÃO: Esta ação irá REMOVER permanentemente todos os deals expirados!',
        'warning',
        () => {
            showConfirmModal(
                'Confirmação Final',
                'Tem certeza que deseja remover os deals expirados? Esta ação não pode ser desfeita.',
                'error',
                () => {
                    fetch('{{ route("admin.cleanup.remove") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const details = {
                                'Deals removidos': data.removed_count || 0,
                                'Deals restantes': data.remaining_count || 0
                            };
                            
                            showNotification(
                                'success',
                                'Limpeza Concluída',
                                'Base de dados limpa com sucesso!',
                                details
                            );
                            
                            // Recarregar a página para atualizar estatísticas
                            setTimeout(() => window.location.reload(), 2000);
                        } else {
                            showNotification('error', 'Erro na Limpeza', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        showNotification('error', 'Erro de Conexão', 'Erro ao remover deals expirados.');
                    });
                }
            );
        }
    );
}

// Função para atualizar imagens
function updateImages() {
    showConfirmModal(
        'Atualizar Imagens',
        'Deseja atualizar as imagens dos jogos? Este processo pode demorar alguns minutos.',
        'info',
        () => {
            showNotification('info', 'Funcionalidade em Desenvolvimento', 'Esta funcionalidade será implementada com jobs em background.');
        }
    );
}
</script>
@endsection
