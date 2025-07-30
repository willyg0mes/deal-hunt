<div class="h-full py-6 px-4">
    <div class="space-y-6">
        <!-- Seção Principal -->
        <div>
            <h3 class="text-xs font-semibold text-gray-400 dark:text-gray-300 uppercase tracking-wider mb-3">
                Principal
            </h3>
            <nav class="space-y-1">
                <!-- Dashboard/Home -->
                <a href="{{ route('home') }}" 
                   class="sidebar-link {{ request()->routeIs('home') ? 'text-white bg-red-600/20 border-r-2 border-red-500 dark:text-white dark:bg-red-600/20' : 'text-gray-300 hover:text-white hover:bg-gray-700/50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600/50' }} group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200">
                    <svg class="mr-3 h-5 w-5 {{ request()->routeIs('home') ? 'text-red-400' : 'text-gray-400 group-hover:text-red-400 dark:text-gray-500 dark:group-hover:text-red-400' }} transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z" />
                    </svg>
                    Ofertas
                </a>

                <!-- Games -->
                <a href="{{ route('games.index') }}" 
                   class="sidebar-link {{ request()->routeIs('games.*') ? 'text-white bg-red-600/20 border-r-2 border-red-500 dark:text-white dark:bg-red-600/20' : 'text-gray-300 hover:text-white hover:bg-gray-700/50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600/50' }} group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200">
                    <svg class="mr-3 h-5 w-5 {{ request()->routeIs('games.*') ? 'text-red-400' : 'text-gray-400 group-hover:text-red-400 dark:text-gray-500 dark:group-hover:text-red-400' }} transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h8m-9-4h10a2 2 0 012 2v8a2 2 0 01-2 2H6a2 2 0 01-2-2v-8a2 2 0 012-2z" />
                    </svg>
                    Jogos
                </a>

                <!-- Lista de Interesse -->
                <a href="{{ route('wishlist.index') }}" 
                   class="sidebar-link {{ request()->routeIs('wishlist.*') ? 'text-white bg-red-600/20 border-r-2 border-red-500 dark:text-white dark:bg-red-600/20' : 'text-gray-300 hover:text-white hover:bg-gray-700/50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600/50' }} group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200">
                    <svg class="mr-3 h-5 w-5 {{ request()->routeIs('wishlist.*') ? 'text-red-400' : 'text-gray-400 group-hover:text-red-400 dark:text-gray-500 dark:group-hover:text-red-400' }} transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    Lista de Interesse
                </a>

                <!-- Jogos Gratuitos -->
                <a href="{{ route('games.free') ?? '#' }}" 
                   class="sidebar-link {{ request()->routeIs('games.free') ? 'text-white bg-red-600/20 border-r-2 border-red-500 dark:text-white dark:bg-red-600/20' : 'text-gray-300 hover:text-white hover:bg-gray-700/50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600/50' }} group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200">
                    <svg class="mr-3 h-5 w-5 {{ request()->routeIs('games.free') ? 'text-red-400' : 'text-gray-400 group-hover:text-red-400 dark:text-gray-500 dark:group-hover:text-red-400' }} transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                    Jogos Gratuitos
                </a>

                <!-- Notificações -->
                <a href="{{ route('notifications.index') }}" 
                   class="sidebar-link {{ request()->routeIs('notifications.*') ? 'text-white bg-red-600/20 border-r-2 border-red-500 dark:text-white dark:bg-red-600/20' : 'text-gray-300 hover:text-white hover:bg-gray-700/50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600/50' }} group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200">
                    <svg class="mr-3 h-5 w-5 {{ request()->routeIs('notifications.*') ? 'text-red-400' : 'text-gray-400 group-hover:text-red-400 dark:text-gray-500 dark:group-hover:text-red-400' }} transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.613 5.79a3.001 3.001 0 012.562-1.559 2.993 2.993 0 012.562 1.559l.853 1.707A1 1 0 0011 8h2a1 1 0 00.41-.086l.853-1.707a3.001 3.001 0 012.562-1.559 2.993 2.993 0 012.562 1.559L20.6 8.4a1 1 0 00.894.6h.506a1 1 0 011 1v10a1 1 0 01-1 1H2a1 1 0 01-1-1V10a1 1 0 011-1h.506a1 1 0 00.894-.6l1.213-2.21z" />
                    </svg>
                    Notificações
                    @if(auth()->user()->notifications()->where('read', false)->count() > 0)
                        <span class="ml-auto bg-red-600 dark:bg-red-500 text-white text-xs rounded-full px-2 py-0.5">
                            {{ auth()->user()->notifications()->where('read', false)->count() }}
                        </span>
                    @endif
                </a>
            </nav>
        </div>

        <!-- Seção do Usuário -->
        <div>
            <h3 class="text-xs font-semibold text-gray-400 dark:text-gray-300 uppercase tracking-wider mb-3">
                Conta
            </h3>
            <nav class="space-y-1">
                <!-- Perfil -->
                <a href="{{ route('profile.edit') }}" 
                   class="sidebar-link {{ request()->routeIs('profile.*') ? 'text-white bg-red-600/20 border-r-2 border-red-500 dark:text-white dark:bg-red-600/20' : 'text-gray-300 hover:text-white hover:bg-gray-700/50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600/50' }} group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200">
                    <svg class="mr-3 h-5 w-5 {{ request()->routeIs('profile.*') ? 'text-red-400' : 'text-gray-400 group-hover:text-red-400 dark:text-gray-500 dark:group-hover:text-red-400' }} transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Perfil
                </a>

                <!-- Dashboard (se autenticado) -->
                @if(Route::has('dashboard'))
                <a href="{{ route('dashboard') }}" 
                   class="sidebar-link {{ request()->routeIs('dashboard') ? 'text-white bg-red-600/20 border-r-2 border-red-500 dark:text-white dark:bg-red-600/20' : 'text-gray-300 hover:text-white hover:bg-gray-700/50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600/50' }} group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200">
                    <svg class="mr-3 h-5 w-5 {{ request()->routeIs('dashboard') ? 'text-red-400' : 'text-gray-400 group-hover:text-red-400 dark:text-gray-500 dark:group-hover:text-red-400' }} transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Dashboard
                </a>
                @endif
            </nav>
        </div>

        <!-- Seção Administrativa (só para admins) -->
        @if(in_array(auth()->user()->email, ['admin@dealhunt.com', 'admin@test.com']))
        <div>
            <h3 class="text-xs font-semibold text-gray-400 dark:text-gray-300 uppercase tracking-wider mb-3">
                Administração
            </h3>
            <nav class="space-y-1">
                <!-- Painel Admin -->
                <a href="{{ route('admin.dashboard') }}" 
                   class="sidebar-link {{ request()->routeIs('admin.*') ? 'text-white bg-red-600/20 border-r-2 border-red-500 dark:text-white dark:bg-red-600/20' : 'text-gray-300 hover:text-white hover:bg-gray-700/50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600/50' }} group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200">
                    <svg class="mr-3 h-5 w-5 {{ request()->routeIs('admin.*') ? 'text-red-400' : 'text-gray-400 group-hover:text-red-400 dark:text-gray-500 dark:group-hover:text-red-400' }} transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Painel Admin
                </a>

                <!-- Usuários -->
                <a href="{{ route('admin.users') }}" 
                   class="sidebar-link {{ request()->routeIs('admin.users') ? 'text-white bg-red-600/20 border-r-2 border-red-500 dark:text-white dark:bg-red-600/20' : 'text-gray-300 hover:text-white hover:bg-gray-700/50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600/50' }} group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200">
                    <svg class="mr-3 h-5 w-5 {{ request()->routeIs('admin.users') ? 'text-red-400' : 'text-gray-400 group-hover:text-red-400 dark:text-gray-500 dark:group-hover:text-red-400' }} transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                    </svg>
                    Usuários
                </a>

                <!-- Logs -->
                <a href="{{ route('admin.logs') }}" 
                   class="sidebar-link {{ request()->routeIs('admin.logs') ? 'text-white bg-red-600/20 border-r-2 border-red-500 dark:text-white dark:bg-red-600/20' : 'text-gray-300 hover:text-white hover:bg-gray-700/50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600/50' }} group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200">
                    <svg class="mr-3 h-5 w-5 {{ request()->routeIs('admin.logs') ? 'text-red-400' : 'text-gray-400 group-hover:text-red-400 dark:text-gray-500 dark:group-hover:text-red-400' }} transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Logs
                </a>
            </nav>
        </div>
        @endif
    </div>
</div>
