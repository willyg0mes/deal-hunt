@extends('admin.layout')

@section('title', 'Logs do Sistema')
@section('header', 'Logs Administrativos')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-red-100 hover:shadow-lg transition-all duration-200">
    <div class="p-6 border-b border-red-200 bg-red-50/30">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Log de Atividades
            </h3>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-600 bg-white px-3 py-1 rounded-full border border-red-200 shadow-sm">
                    Total: {{ $logs->total() }} registros
                </span>
                <button onclick="clearOldLogs()" 
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 text-sm transition-all duration-200 shadow-sm hover:shadow">
                    Limpar Logs Antigos
                </button>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-red-50/50 border-b border-red-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">
                        Data/Hora
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">
                        Usuário
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">
                        Ação
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">
                        Entidade
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">
                        IP
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">
                        Detalhes
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-red-100">
                @forelse($logs as $log)
                    <tr class="hover:bg-red-50/30 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 font-medium">
                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $log->created_at->diffForHumans() }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($log->user)
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gradient-to-r from-red-500 to-red-600 rounded-full flex items-center justify-center text-white text-xs font-medium mr-2 shadow-sm">
                                        {{ substr($log->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $log->user->name }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            ID: {{ $log->user->id }}
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gray-400 rounded-full flex items-center justify-center text-white text-xs font-medium mr-2">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <span class="text-sm text-gray-500 italic font-medium">Sistema</span>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $log->action }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($log->entity_type)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                    {{ $log->entity_type }}
                                    @if($log->entity_id)
                                        #{{ $log->entity_id }}
                                    @endif
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                {{ $log->ip_address ?: '-' }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($log->details)
                                <div class="text-sm text-gray-600">
                                    @if(is_array($log->details))
                                        <details class="cursor-pointer">
                                            <summary class="text-red-600 hover:text-red-800 cursor-pointer">Ver detalhes</summary>
                                            <div class="mt-2 p-2 bg-gray-50 rounded text-xs">
                                                @foreach($log->details as $key => $value)
                                                    <div><strong>{{ $key }}:</strong> {{ is_array($value) ? json_encode($value) : $value }}</div>
                                                @endforeach
                                            </div>
                                        </details>
                                    @else
                                        {{ $log->details }}
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            Nenhum log encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
        <div class="px-6 py-4 border-t border-red-200 bg-red-50/20">
            {{ $logs->links() }}
        </div>
    @endif
</div>

<div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-6">
    <!-- Estatísticas dos Logs -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-red-100 hover:shadow-lg hover:border-red-200 transition-all duration-200">
        <h4 class="font-semibold mb-3 text-gray-900 flex items-center">
            <svg class="w-4 h-4 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Total de Logs
        </h4>
        <div class="text-2xl font-bold text-red-600">
            {{ $logs->total() }}
        </div>
        <p class="text-sm text-gray-600 mt-1">
            Registros no sistema
        </p>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-red-100 hover:shadow-lg hover:border-red-200 transition-all duration-200">
        <h4 class="font-semibold mb-3 text-gray-900 flex items-center">
            <svg class="w-4 h-4 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707"/>
            </svg>
            Hoje
        </h4>
        <div class="text-2xl font-bold text-red-600">
            {{ $logs->where('created_at', '>=', today())->count() }}
        </div>
        <p class="text-sm text-gray-600 mt-1">
            Atividades de hoje
        </p>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-red-100 hover:shadow-lg hover:border-red-200 transition-all duration-200">
        <h4 class="font-semibold mb-3 text-gray-900 flex items-center">
            <svg class="w-4 h-4 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Esta Semana
        </h4>
        <div class="text-2xl font-bold text-red-600">
            {{ $logs->where('created_at', '>=', now()->startOfWeek())->count() }}
        </div>
        <p class="text-sm text-gray-600 mt-1">
            Últimos 7 dias
        </p>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-red-100 hover:shadow-lg hover:border-red-200 transition-all duration-200">
        <h4 class="font-semibold mb-3 text-gray-900 flex items-center">
            <svg class="w-4 h-4 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            Usuários Ativos
        </h4>
        <div class="text-2xl font-bold text-red-600">
            {{ $logs->pluck('user_id')->unique()->filter()->count() }}
        </div>
        <p class="text-sm text-gray-600 mt-1">
            Usuários distintos
        </p>
    </div>
</div>

<script>
function clearOldLogs() {
    if (confirm('Deseja remover logs com mais de 30 dias? Esta ação não pode ser desfeita.')) {
        // Implementar AJAX para limpar logs antigos
        alert('Funcionalidade será implementada');
    }
}
</script>
@endsection
