<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - Deal Hunt</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Sidebar Desktop - Parte do layout normal -->
        <aside class="sidebar w-64 bg-gradient-to-b from-gray-900 via-red-900 to-red-800 shadow-2xl flex flex-col hidden lg:block h-screen overflow-y-auto">
            <!-- Logo -->
            <div class="p-6 border-b border-red-700/50">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-red-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h1 class="text-xl font-bold text-white">Deal Hunt</h1>
                        <p class="text-sm text-red-200">Painel Admin</p>
                    </div>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                <a href="{{ route('admin.dashboard') }}" 
                   class="sidebar-item group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-red-600 text-white shadow-lg scale-105 nav-active' : 'text-red-100 hover:bg-red-800/50 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-red-300 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2H3a2 2 0 00-2 2v2a2 2 0 002 2z"/>
                    </svg>
                    Dashboard
                    @if(request()->routeIs('admin.dashboard'))
                        <div class="ml-auto w-2 h-2 bg-white rounded-full"></div>
                    @endif
                </a>
                
                <a href="{{ route('admin.users') }}" 
                   class="sidebar-item group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('admin.users*') ? 'bg-red-600 text-white shadow-lg scale-105 nav-active' : 'text-red-100 hover:bg-red-800/50 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.users*') ? 'text-white' : 'text-red-300 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Usuários
                    @if(request()->routeIs('admin.users*'))
                        <div class="ml-auto w-2 h-2 bg-white rounded-full"></div>
                    @endif
                </a>
                
                <!-- Divider -->
                <div class="py-2">
                    <div class="border-t border-red-700/50"></div>
                </div>

                <!-- System Section -->
                <div class="space-y-2">
                    <p class="px-4 text-xs font-semibold text-red-300 uppercase tracking-wider">
                        Sistema
                    </p>
                    
                    <a href="{{ route('admin.translations.index') }}" 
                       class="sidebar-item group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('admin.translations*') ? 'bg-red-600 text-white shadow-lg scale-105 nav-active' : 'text-red-100 hover:bg-red-800/50 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.translations*') ? 'text-white' : 'text-red-300 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                        </svg>
                        Traduções
                        @if(request()->routeIs('admin.translations*'))
                            <div class="ml-auto w-2 h-2 bg-white rounded-full"></div>
                        @endif
                    </a>

                    <a href="{{ route('admin.currency.index') }}" 
                       class="sidebar-item group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('admin.currency*') ? 'bg-red-600 text-white shadow-lg scale-105 nav-active' : 'text-red-100 hover:bg-red-800/50 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.currency*') ? 'text-white' : 'text-red-300 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Moedas & Taxas
                        @if(request()->routeIs('admin.currency*'))
                            <div class="ml-auto w-2 h-2 bg-white rounded-full"></div>
                        @endif
                    </a>

                    <a href="{{ route('admin.free-games.index') }}" 
                       class="sidebar-item group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('admin.free-games*') ? 'bg-red-600 text-white shadow-lg scale-105 nav-active' : 'text-red-100 hover:bg-red-800/50 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.free-games*') ? 'text-white' : 'text-red-300 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Jogos Gratuitos
                        @if(request()->routeIs('admin.free-games*'))
                            <div class="ml-auto w-2 h-2 bg-white rounded-full"></div>
                        @endif
                    </a>

                    <a href="{{ route('admin.controls') }}" 
                       class="sidebar-item group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('admin.controls*') ? 'bg-red-600 text-white shadow-lg scale-105 nav-active' : 'text-red-100 hover:bg-red-800/50 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.controls*') ? 'text-white' : 'text-red-300 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"/>
                        </svg>
                        Controles do Sistema
                        @if(request()->routeIs('admin.controls*'))
                            <div class="ml-auto w-2 h-2 bg-white rounded-full"></div>
                        @endif
                    </a>
                    
                    <a href="{{ route('admin.logs') }}" 
                       class="sidebar-item group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('admin.logs*') ? 'bg-red-600 text-white shadow-lg scale-105 nav-active' : 'text-red-100 hover:bg-red-800/50 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.logs*') ? 'text-white' : 'text-red-300 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Logs de Atividade
                        @if(request()->routeIs('admin.logs*'))
                            <div class="ml-auto w-2 h-2 bg-white rounded-full"></div>
                        @endif
                    </a>
                </div>

                <!-- Divider -->
                <div class="py-2">
                    <div class="border-t border-red-700/50"></div>
                </div>

                <!-- Games Section -->
                <div class="space-y-2">
                    <p class="px-4 text-xs font-semibold text-red-300 uppercase tracking-wider">
                        Gerenciar Jogos
                    </p>
                    
                    <button id="fetch-deals-btn" 
                            class="group w-full flex items-center px-4 py-3 text-sm font-medium rounded-xl text-red-100 hover:bg-red-800/50 hover:text-white transition-all duration-200">
                        <svg class="w-5 h-5 mr-3 text-red-300 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Buscar Novos Deals
                        <span id="loading-spinner" class="ml-auto hidden">
                            <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </button>
                    <button id="fetch-free-games-btn" 
                            class="group w-full flex items-center px-4 py-3 text-sm font-medium rounded-xl text-red-100 hover:bg-red-800/50 hover:text-white transition-all duration-200">
                        <span class="mr-3">🆓</span>
                        Buscar Jogos Gratuitos
                        <span id="loading-spinner-free" class="ml-auto hidden">
                            <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </button>
                    <a href="{{ route('games.index') }}" 
                       class="group flex items-center px-4 py-3 text-sm font-medium rounded-xl text-red-100 hover:bg-red-800/50 hover:text-white transition-all duration-200">
                        <svg class="w-5 h-5 mr-3 text-red-300 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Ver Site Público
                        <svg class="w-4 h-4 ml-auto text-red-300 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                </div>
            </nav>
            
            <!-- User Info -->
            <div class="p-4 border-t border-red-700/50 bg-gradient-to-r from-red-800 to-red-900">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-red-500 to-red-600 rounded-full flex items-center justify-center text-white text-lg font-bold shadow-lg">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="ml-3 flex-1 min-w-0">
                        <p class="text-sm font-bold text-white truncate">
                            {{ auth()->user()->name }}
                        </p>
                        <p class="text-xs text-red-200 truncate flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            Administrador
                        </p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="ml-2">
                        @csrf
                        <button type="submit" class="p-2 text-red-200 hover:text-white hover:bg-red-700/50 rounded-lg transition-all duration-200" title="Sair do painel">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Overlay (for mobile sidebar) -->
        <div id="admin-sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden transition-opacity duration-300"></div>

        <!-- Admin Mobile Sidebar (Fixed, off-canvas) -->
        <div id="admin-mobile-sidebar" class="fixed top-0 left-0 bottom-0 z-50 w-[85%] max-w-sm h-full min-h-screen bg-gradient-to-b from-gray-900 via-red-900 to-red-800 shadow-2xl -translate-x-full transition-transform duration-300 ease-in-out lg:hidden rounded-r-2xl border-r-4 border-red-700/30 flex flex-col" style="transform: translateX(-100%); height: 100vh; max-height: 100vh;">
            <!-- Logo -->
            <div class="p-4 sm:p-6 border-b border-red-700/50 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-red-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h1 class="text-xl font-bold text-white">Deal Hunt</h1>
                        <p class="text-sm text-red-200">Painel Admin</p>
                    </div>
                </div>
                <button id="admin-sidebar-close" class="p-2 text-red-200 hover:text-white hover:bg-red-700/50 rounded-lg transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 p-4 space-y-2 overflow-y-auto min-h-0">
                <a href="{{ route('admin.dashboard') }}" 
                   class="sidebar-item group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-red-600 text-white shadow-lg scale-105 nav-active' : 'text-red-100 hover:bg-red-800/50 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-red-300 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2H3a2 2 0 00-2 2v2a2 2 0 002 2z"/>
                    </svg>
                    Dashboard
                    @if(request()->routeIs('admin.dashboard'))
                        <div class="ml-auto w-2 h-2 bg-white rounded-full"></div>
                    @endif
                </a>
                
                <a href="{{ route('admin.users') }}" 
                   class="sidebar-item group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('admin.users*') ? 'bg-red-600 text-white shadow-lg scale-105 nav-active' : 'text-red-100 hover:bg-red-800/50 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.users*') ? 'text-white' : 'text-red-300 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Usuários
                    @if(request()->routeIs('admin.users*'))
                        <div class="ml-auto w-2 h-2 bg-white rounded-full"></div>
                    @endif
                </a>
                
                <!-- Divider -->
                <div class="py-2">
                    <div class="border-t border-red-700/50"></div>
                </div>

                <!-- System Section -->
                <div class="space-y-2">
                    <p class="px-4 text-xs font-semibold text-red-300 uppercase tracking-wider">
                        Sistema
                    </p>
                    
                    <a href="{{ route('admin.translations.index') }}" 
                       class="sidebar-item group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('admin.translations*') ? 'bg-red-600 text-white shadow-lg scale-105 nav-active' : 'text-red-100 hover:bg-red-800/50 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.translations*') ? 'text-white' : 'text-red-300 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                        </svg>
                        Traduções
                        @if(request()->routeIs('admin.translations*'))
                            <div class="ml-auto w-2 h-2 bg-white rounded-full"></div>
                        @endif
                    </a>

                    <a href="{{ route('admin.currency.index') }}" 
                       class="sidebar-item group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('admin.currency*') ? 'bg-red-600 text-white shadow-lg scale-105 nav-active' : 'text-red-100 hover:bg-red-800/50 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.currency*') ? 'text-white' : 'text-red-300 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Moedas & Taxas
                        @if(request()->routeIs('admin.currency*'))
                            <div class="ml-auto w-2 h-2 bg-white rounded-full"></div>
                        @endif
                    </a>

                    <a href="{{ route('admin.free-games.index') }}" 
                       class="sidebar-item group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('admin.free-games*') ? 'bg-red-600 text-white shadow-lg scale-105 nav-active' : 'text-red-100 hover:bg-red-800/50 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.free-games*') ? 'text-white' : 'text-red-300 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Jogos Gratuitos
                        @if(request()->routeIs('admin.free-games*'))
                            <div class="ml-auto w-2 h-2 bg-white rounded-full"></div>
                        @endif
                    </a>

                    <a href="{{ route('admin.controls') }}" 
                       class="sidebar-item group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('admin.controls*') ? 'bg-red-600 text-white shadow-lg scale-105 nav-active' : 'text-red-100 hover:bg-red-800/50 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.controls*') ? 'text-white' : 'text-red-300 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"/>
                        </svg>
                        Controles do Sistema
                        @if(request()->routeIs('admin.controls*'))
                            <div class="ml-auto w-2 h-2 bg-white rounded-full"></div>
                        @endif
                    </a>
                    
                    <a href="{{ route('admin.logs') }}" 
                       class="sidebar-item group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('admin.logs*') ? 'bg-red-600 text-white shadow-lg scale-105 nav-active' : 'text-red-100 hover:bg-red-800/50 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.logs*') ? 'text-white' : 'text-red-300 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Logs de Atividade
                        @if(request()->routeIs('admin.logs*'))
                            <div class="ml-auto w-2 h-2 bg-white rounded-full"></div>
                        @endif
                    </a>
                </div>

                <!-- Divider -->
                <div class="py-2">
                    <div class="border-t border-red-700/50"></div>
                </div>

                <!-- Games Section -->
                <div class="space-y-2">
                    <p class="px-4 text-xs font-semibold text-red-300 uppercase tracking-wider">
                        Gerenciar Jogos
                    </p>
                    
                    <button id="fetch-deals-btn-mobile" 
                            class="group w-full flex items-center px-4 py-3 text-sm font-medium rounded-xl text-red-100 hover:bg-red-800/50 hover:text-white transition-all duration-200">
                        <svg class="w-5 h-5 mr-3 text-red-300 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Buscar Novos Deals
                        <span id="loading-spinner-mobile" class="ml-auto hidden">
                            <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </button>
                    <button id="fetch-free-games-btn-mobile" 
                            class="group w-full flex items-center px-4 py-3 text-sm font-medium rounded-xl text-red-100 hover:bg-red-800/50 hover:text-white transition-all duration-200">
                        <span class="mr-3">🆓</span>
                        Buscar Jogos Gratuitos
                        <span id="loading-spinner-free-mobile" class="ml-auto hidden">
                            <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </button>
                    <a href="{{ route('games.index') }}" 
                       class="group flex items-center px-4 py-3 text-sm font-medium rounded-xl text-red-100 hover:bg-red-800/50 hover:text-white transition-all duration-200">
                        <svg class="w-5 h-5 mr-3 text-red-300 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Ver Site Público
                        <svg class="w-4 h-4 ml-auto text-red-300 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                </div>
            </nav>
            
            <!-- User Info -->
            <div class="mt-auto p-4 border-t border-red-700/50 bg-gradient-to-r from-red-800 to-red-900 flex-shrink-0 rounded-br-2xl">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-red-500 to-red-600 rounded-full flex items-center justify-center text-white text-lg font-bold shadow-lg">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="ml-3 flex-1 min-w-0">
                        <p class="text-sm font-bold text-white truncate">
                            {{ auth()->user()->name }}
                        </p>
                        <p class="text-xs text-red-200 truncate flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            Administrador
                        </p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="ml-2">
                        @csrf
                        <button type="submit" class="p-2 text-red-200 hover:text-white hover:bg-red-700/50 rounded-lg transition-all duration-200" title="Sair do painel">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Content - Scrollable -->
        <main class="main-content flex-1 lg:ml-64 min-h-screen bg-gradient-to-br from-red-50 to-gray-50 transition-all duration-300 relative z-10">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-red-200 sticky top-0 z-20">
                <div class="px-4 sm:px-6 py-4 bg-gradient-to-r from-white to-red-50/30">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <!-- Mobile Sidebar Toggle -->
                            <button id="admin-sidebar-toggle" class="lg:hidden flex p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors" title="Abrir menu lateral">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                </svg>
                            </button>
                            <div>
                                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">@yield('header', 'Dashboard')</h1>
                                <p class="text-xs sm:text-sm text-gray-600 mt-1">@yield('description', 'Painel de controle administrativo')</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="hidden lg:flex items-center space-x-2 text-sm text-gray-500 bg-gray-50 px-3 py-2 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>{{ now()->format('d/m/Y H:i') }}</span>
                            </div>
                            <!-- Mobile User Menu -->
                            <div class="lg:hidden relative" x-data="{ open: false }">
                                <button @click="open = !open" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-red-500">
                                    <div class="w-8 h-8 bg-gradient-to-r from-red-500 to-red-600 rounded-full flex items-center justify-center text-white text-sm font-bold shadow-lg">
                                        {{ substr(auth()->user()->name, 0, 1) }}
                                    </div>
                                </button>
                                <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 py-2 z-50">
                                    <div class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                                        <div class="font-medium">{{ auth()->user()->name }}</div>
                                        <div class="text-xs text-gray-500">Administrador</div>
                                    </div>
                                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        Meu Perfil
                                    </a>
                                    <a href="{{ route('home') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        Voltar ao Site
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                            Sair
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="p-6">
                @if(session('success'))
                    <div class="mb-6 bg-green-50 border-l-4 border-green-400 text-green-800 px-4 py-3 rounded-r-lg shadow-sm hover:shadow transition-shadow duration-200">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            {{ session('success') }}
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 bg-red-50 border-l-4 border-red-400 text-red-800 px-4 py-3 rounded-r-lg shadow-sm hover:shadow transition-shadow duration-200">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            {{ session('error') }}
                        </div>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script>
        // Fetch deals functionality with improved UX
        document.getElementById('fetch-deals-btn').addEventListener('click', function() {
            const btn = this;
            const spinner = document.getElementById('loading-spinner');
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            spinner.classList.remove('hidden');
            btn.classList.add('bg-red-700', 'scale-95');
            
            fetch('/admin/fetch-deals', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ limit: 50 })
            })
            .then(response => {
                if (!response.ok && !response.headers.get('content-type')?.includes('application/json')) {
                    throw new Error('Resposta inválida do servidor. Tente recarregar a página.');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Show success toast
                    showToast('✅ ' + data.message, 'success');
                    
                    // Add success animation
                    btn.classList.add('bg-green-600');
                    setTimeout(() => {
                        btn.classList.remove('bg-green-600');
                    }, 2000);
                } else {
                    showToast('❌ Erro: ' + (data.message || 'Falha na busca'), 'error');
                }
            })
            .catch(error => {
                showToast('❌ Erro de conexão: ' + error.message, 'error');
            })
            .finally(() => {
                btn.disabled = false;
                spinner.classList.add('hidden');
                btn.classList.remove('bg-red-700', 'scale-95');
            });
        });

        // Buscar apenas jogos gratuitos permanentes (RAWG)
        function runFetchFreeGames(btn, spinnerId) {
            const spinner = document.getElementById(spinnerId);
            if (!btn || btn.disabled) return;
            btn.disabled = true;
            if (spinner) spinner.classList.remove('hidden');
            fetch('{{ route("admin.fetch-free-games") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ limit: 80 })
            })
            .then(response => {
                if (!response.ok && !response.headers.get('content-type')?.includes('application/json')) {
                    throw new Error('Resposta inválida do servidor.');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showToast('✅ ' + data.message, 'success');
                } else {
                    showToast('❌ ' + (data.message || 'Falha na busca'), 'error');
                }
            })
            .catch(err => showToast('❌ Erro: ' + err.message, 'error'))
            .finally(() => {
                btn.disabled = false;
                if (spinner) spinner.classList.add('hidden');
            });
        }
        const fetchFreeBtn = document.getElementById('fetch-free-games-btn');
        if (fetchFreeBtn) {
            fetchFreeBtn.addEventListener('click', function() { runFetchFreeGames(this, 'loading-spinner-free'); });
        }
        const fetchFreeBtnMobile = document.getElementById('fetch-free-games-btn-mobile');
        if (fetchFreeBtnMobile) {
            fetchFreeBtnMobile.addEventListener('click', function() { runFetchFreeGames(this, 'loading-spinner-free-mobile'); });
        }

        // Toast notification system
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-600' : type === 'error' ? 'bg-red-600' : 'bg-blue-600';
            
            toast.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-4 rounded-lg shadow-2xl z-50 transform translate-x-full transition-transform duration-300 ease-out max-w-sm`;
            toast.innerHTML = `
                <div class="flex items-center">
                    <span class="mr-2">${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white/80 hover:text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            // Animate in
            setTimeout(() => {
                toast.classList.remove('translate-x-full');
            }, 100);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.remove();
                    }
                }, 300);
            }, 5000);
        }
    </script>

    <!-- Admin Sidebar Control Script -->
    <script>
        // Admin sidebar toggle
        const adminSidebarToggle = document.getElementById('admin-sidebar-toggle');
        const adminSidebar = document.getElementById('admin-mobile-sidebar');
        const adminSidebarClose = document.getElementById('admin-sidebar-close');
        const adminSidebarOverlay = document.getElementById('admin-sidebar-overlay');

        function openAdminSidebar() {
            if (adminSidebar) {
                adminSidebar.style.transform = 'translateX(0)';
                adminSidebar.classList.remove('-translate-x-full');
            }
            if (adminSidebarOverlay) {
                adminSidebarOverlay.classList.remove('hidden');
            }
            document.body.style.overflow = 'hidden';
        }

        function closeAdminSidebar() {
            if (adminSidebar) {
                adminSidebar.style.transform = 'translateX(-100%)';
                adminSidebar.classList.add('-translate-x-full');
            }
            if (adminSidebarOverlay) {
                adminSidebarOverlay.classList.add('hidden');
            }
            document.body.style.overflow = '';
        }

        // Garantir que o sidebar esteja fechado ao carregar a página
        document.addEventListener('DOMContentLoaded', function() {
            closeAdminSidebar();
        });

        if (adminSidebarToggle) {
            adminSidebarToggle.addEventListener('click', openAdminSidebar);
        }

        if (adminSidebarClose) {
            adminSidebarClose.addEventListener('click', closeAdminSidebar);
        }

        if (adminSidebarOverlay) {
            adminSidebarOverlay.addEventListener('click', closeAdminSidebar);
        }

        // Fetch deals functionality for mobile
        const fetchDealsBtnMobile = document.getElementById('fetch-deals-btn-mobile');
        if (fetchDealsBtnMobile) {
            fetchDealsBtnMobile.addEventListener('click', function() {
                const btn = this;
                const spinner = document.getElementById('loading-spinner-mobile');
                const originalText = btn.innerHTML;
                
                btn.disabled = true;
                spinner.classList.remove('hidden');
                btn.classList.add('bg-red-700', 'scale-95');
                
                fetch('/admin/fetch-deals', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ limit: 50 })
                })
                .then(response => {
                    if (!response.ok && !response.headers.get('content-type')?.includes('application/json')) {
                        throw new Error('Resposta inválida do servidor. Tente recarregar a página.');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showToast('✅ ' + data.message, 'success');
                        btn.classList.add('bg-green-600');
                        setTimeout(() => {
                            btn.classList.remove('bg-green-600');
                        }, 2000);
                    } else {
                        showToast('❌ Erro: ' + (data.message || 'Falha na busca'), 'error');
                    }
                })
                .catch(error => {
                    showToast('❌ Erro de conexão: ' + error.message, 'error');
                })
                .finally(() => {
                    btn.disabled = false;
                    spinner.classList.add('hidden');
                    btn.classList.remove('bg-red-700', 'scale-95');
                    closeAdminSidebar();
                });
            });
        }

        // Close sidebar when clicking on a link
        const adminSidebarLinks = adminSidebar?.querySelectorAll('a');
        if (adminSidebarLinks) {
            adminSidebarLinks.forEach(link => {
                link.addEventListener('click', () => {
                    closeAdminSidebar();
                });
            });
        }
    </script>

    <!-- Scripts adicionais das páginas -->
    @stack('scripts')
</body>
</html>
