@extends('admin.layout')

@section('title', 'Usuários')
@section('header', 'Gerenciamento de Usuários')
@section('description', 'Gerencie usuários e permissões do sistema')

@section('content')
<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-red-100 p-6 hover:shadow-lg hover:border-red-200 transition-all duration-200 stat-card">
        <div class="flex items-center">
            <div class="p-3 rounded-xl bg-gradient-to-r from-red-100 to-red-200">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total de Usuários</p>
                <p class="text-2xl font-bold text-gray-900">{{ $users->total() }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-red-100 p-6 hover:shadow-lg hover:border-red-200 transition-all duration-200 stat-card">
        <div class="flex items-center">
            <div class="p-3 rounded-xl bg-gradient-to-r from-red-100 to-red-200">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Administradores</p>
                <p class="text-2xl font-bold text-gray-900">{{ $users->where('is_admin', true)->count() }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-red-100 p-6 hover:shadow-lg hover:border-red-200 transition-all duration-200 stat-card">
        <div class="flex items-center">
            <div class="p-3 rounded-xl bg-gradient-to-r from-red-100 to-red-200">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Emails Verificados</p>
                <p class="text-2xl font-bold text-gray-900">{{ $users->whereNotNull('email_verified_at')->count() }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-red-100 p-6 hover:shadow-lg hover:border-red-200 transition-all duration-200 stat-card">
        <div class="flex items-center">
            <div class="p-3 rounded-xl bg-gradient-to-r from-red-100 to-red-200">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Novos (30 dias)</p>
                <p class="text-2xl font-bold text-gray-900">{{ $users->where('created_at', '>=', now()->subDays(30))->count() }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="bg-white rounded-xl shadow-sm border border-red-100 overflow-hidden hover:shadow-lg transition-all duration-200">
    <div class="px-6 py-4 border-b border-red-200 bg-red-50/30">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Lista de Usuários
            </h3>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-600 bg-white px-3 py-1 rounded-full border border-red-200 shadow-sm">
                    {{ $users->total() }} usuários cadastrados
                </span>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-red-50/50 border-b border-red-200">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-red-700 uppercase tracking-wider">
                        Usuário
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-red-700 uppercase tracking-wider">
                        Contato
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-red-700 uppercase tracking-wider">
                        Cadastro
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-red-700 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-red-700 uppercase tracking-wider">
                        Ações
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-50 transition-colors table-row-hover">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="relative">
                                    <div class="w-12 h-12 bg-gradient-to-r from-red-500 to-red-600 rounded-full flex items-center justify-center text-white font-semibold text-lg shadow-sm">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    @if($user->is_admin)
                                        <div class="absolute -top-1 -right-1 w-5 h-5 bg-red-400 rounded-full flex items-center justify-center">
                                            <svg class="w-3 h-3 text-red-800" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-semibold text-gray-900">
                                        {{ $user->name }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        ID: #{{ $user->id }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $user->email }}</div>
                            @if($user->email_verified_at)
                                <div class="flex items-center mt-1">
                                    <svg class="w-3 h-3 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-xs text-green-600 font-medium">Verificado</span>
                                </div>
                            @else
                                <div class="flex items-center mt-1">
                                    <svg class="w-3 h-3 text-red-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-xs text-red-600 font-medium">Não verificado</span>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 font-medium">
                                {{ $user->created_at->format('d/m/Y') }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $user->created_at->diffForHumans() }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($user->is_admin)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-red-100 to-red-200 text-red-800 border border-red-300 shadow-sm">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    Administrador
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                    </svg>
                                    Usuário
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.toggle-admin', $user) }}" class="inline">
                                    @csrf
                                    @if($user->is_admin)
                                        <button type="submit" 
                                                class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 transition-all duration-200 shadow-sm hover:shadow"
                                                onclick="return confirm('Remover privilégios de administrador de {{ $user->name }}?')">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Remover Admin
                                        </button>
                                    @else
                                        <button type="submit" 
                                                class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 transition-all duration-200 shadow-sm hover:shadow"
                                                onclick="return confirm('Promover {{ $user->name }} a administrador?')">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                            </svg>
                                            Tornar Admin
                                        </button>
                                    @endif
                                </form>
                            @else
                                <span class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg text-gray-500 bg-gray-50 border border-gray-200">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Você mesmo
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <p class="text-gray-500 text-lg font-medium">Nenhum usuário encontrado</p>
                                <p class="text-gray-400 text-sm mt-1">Não há usuários cadastrados no sistema</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
