@php
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;

$unreadCount = 0;
$recentNotifications = collect();

if (Auth::check()) {
$unreadCount = Auth::user()->notifications()->where('read', false)->count();
$recentNotifications = Auth::user()
->notifications()
->orderByDesc('created_at')
->take(10)
->get()
->groupBy(function ($n) {
$date = Carbon::parse($n->created_at);
if ($date->isToday()) return 'Hoje';
if ($date->isYesterday()) return 'Ontem';
return $date->format('d/m/Y');
});
}
@endphp


<nav x-data="{ open: false }" class="fixed top-0 left-0 right-0 z-50 bg-gradient-to-r from-gray-900 via-red-900 to-red-700 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 border-b-2 border-red-700 dark:border-gray-600 shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="shrink-0 flex items-center">
                <a href="{{ route('home') }}">
                    <!-- SVG temporário como logo -->
                    <svg class="h-9 w-auto" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="20" cy="20" r="18" fill="#DC2626" stroke="#fff" stroke-width="2"/>
                        <text x="50%" y="55%" text-anchor="middle" fill="#fff" font-size="16" font-family="Arial" dy=".3em">DH</text>
                    </svg>
                </a>
            </div>

            <!-- Container da direita -->
            <div class="flex items-center gap-4">

                <!-- Menu do usuário -->
                <div id="desktop-user-menu" class="hidden lg:flex lg:items-center gap-4">
                <!-- 🌙 Toggle Dark Mode Desktop (sempre visível em lg+) -->
                <button 
                    id="dark-mode-toggle"
                    class="relative text-gray-300 dark:text-gray-400 hover:text-white dark:hover:text-gray-200 transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-yellow-400 dark:focus:ring-gray-500 focus:ring-opacity-50 rounded-lg p-2"
                    title="Alternar modo escuro">
                    <svg class="w-5 h-5 dark-mode-icon sun-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
                    </svg>
                    <svg class="w-5 h-5 dark-mode-icon moon-icon hidden" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                    </svg>
                </button>

                <!-- 🔓 Menu do usuário -->
                <div class="relative">
                    <button 
                        data-dropdown="userMenu"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-300 dark:text-gray-300 bg-transparent hover:text-white dark:hover:text-white hover:bg-gray-700/50 dark:hover:bg-gray-800/50 focus:outline-none transition ease-in-out duration-150">
                        @auth
                        <img src="{{ asset(Auth::user()->profile_photo_url ?? 'images/default-avatar.png') }}"
                             alt="Foto de perfil"
                             class="w-8 h-8 rounded-full object-cover border-2 border-gray-300 dark:border-gray-500 shadow mr-2" />
                        @endauth
                        <div class="text-gray-300 dark:text-gray-300">{{ Auth::check() ? Auth::user()->name : 'Entrar' }}</div>
                        <div class="ms-1">
                            <svg class="fill-current h-4 w-4 text-gray-300 dark:text-gray-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </button>

                    <div 
                        data-dropdown-content="userMenu"
                        class="hidden absolute right-0 z-50 mt-2 w-56 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 dark:ring-gray-600 transition-all duration-200">
                        <div class="py-1">
                            @auth
                            <a href="{{ route('notifications.index') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <span>🔔</span>
                                <span>Notificações</span>
                            </a>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <span>👤</span>
                                <span>Perfil</span>
                            </a>
                            
                            <!-- Link para Painel Administrativo (apenas para admins) -->
                            @if(auth()->user()->is_admin)
                                <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>
                                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    <span>🛠️</span>
                                    <span>Painel Administrativo</span>
                                </a>
                            @endif

                            <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>
                            
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center gap-2 w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    <span>🚪</span>
                                    <span>Sair</span>
                                </button>
                            </form>
                            @else
                            <a href="{{ route('login') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <span>🔑</span>
                                <span>Entrar</span>
                            </a>
                            <a href="{{ route('login', ['register' => 1]) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <span>✨</span>
                                <span>Criar conta</span>
                            </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>

            <!-- Menu mobile -->
            <div id="mobile-menu-container" class="flex lg:hidden items-center gap-1.5">
                <!-- Dark mode toggle mobile -->
                <button
                    id="dark-mode-toggle-mobile"
                    class="text-gray-300 dark:text-gray-400 hover:text-white dark:hover:text-gray-200 transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-yellow-400 dark:focus:ring-gray-500 focus:ring-opacity-50 rounded-lg p-2"
                    title="Alternar modo escuro">
                    <svg class="w-5 h-5 dark-mode-icon sun-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
                    </svg>
                    <svg class="w-5 h-5 dark-mode-icon moon-icon hidden" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                    </svg>
                </button>
                
                <!-- Foto do usuário (mobile) - integrada ao menu hamburger -->
                @auth
                <div class="flex items-center -mr-1">
                    <img src="{{ asset(Auth::user()->profile_photo_url ?? 'images/default-avatar.png') }}"
                         alt="Foto de perfil"
                         class="w-8 h-8 rounded-full object-cover border-2 border-gray-300 dark:border-gray-500 shadow" />
                </div>
                @endauth
                
                <!-- Menu lateral mobile completo (único botão hamburger) -->
                <div class="relative" x-data="{ sidebarOpen: false }">
                    <button @click="sidebarOpen = !sidebarOpen" class="inline-flex items-center justify-center p-2 rounded-md text-gray-300 dark:text-gray-400 hover:text-white dark:hover:text-gray-200 hover:bg-gray-700/50 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-700/50 dark:focus:bg-gray-800 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': sidebarOpen, 'inline-flex': ! sidebarOpen }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! sidebarOpen, 'inline-flex': sidebarOpen }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <!-- Dropdown menu mobile completo -->
                    <div x-show="sidebarOpen" @click.outside="sidebarOpen = false" x-transition class="absolute right-0 mt-2 w-64 bg-gray-800 dark:bg-gray-900 rounded-lg shadow-xl border border-gray-700 dark:border-gray-600 py-2 z-50">
                        @auth
                        <!-- Menu lateral mobile -->
                        <div class="px-4 py-2 border-b border-gray-700">
                            <h3 class="text-sm font-medium text-gray-300 dark:text-gray-400">Menu</h3>
                        </div>

                        <a href="{{ route('home') }}" class="flex items-center px-4 py-3 text-sm text-gray-300 dark:text-gray-400 hover:text-white dark:hover:text-gray-200 hover:bg-gray-700 dark:hover:bg-gray-800">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            Início
                        </a>

                        <a href="{{ route('games.index') }}" class="flex items-center px-4 py-3 text-sm text-gray-300 dark:text-gray-400 hover:text-white dark:hover:text-gray-200 hover:bg-gray-700 dark:hover:bg-gray-800">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                            Jogos
                        </a>

                        <a href="{{ route('wishlist.index') }}" class="flex items-center px-4 py-3 text-sm text-gray-300 dark:text-gray-400 hover:text-white dark:hover:text-gray-200 hover:bg-gray-700 dark:hover:bg-gray-800">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            Minha Wishlist
                        </a>

                        <a href="{{ route('games.free') }}" class="flex items-center px-4 py-3 text-sm text-gray-300 dark:text-gray-400 hover:text-white dark:hover:text-gray-200 hover:bg-gray-700 dark:hover:bg-gray-800">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                            Jogos Gratuitos
                        </a>

                        <a href="{{ route('notifications.index') }}" class="flex items-center px-4 py-3 text-sm text-gray-300 dark:text-gray-400 hover:text-white dark:hover:text-gray-200 hover:bg-gray-700 dark:hover:bg-gray-800">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            Notificações
                            @if ($unreadCount > 0)
                            <span class="ml-auto bg-red-600 dark:bg-red-500 text-white text-xs font-bold rounded-full px-2 py-1">{{ $unreadCount }}</span>
                            @endif
                        </a>

                        <div class="border-t border-gray-700 my-2"></div>

                        <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-3 text-sm text-gray-300 dark:text-gray-400 hover:text-white dark:hover:text-gray-200 hover:bg-gray-700 dark:hover:bg-gray-800">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Meu Perfil
                        </a>

                        @if(auth()->user()->is_admin)
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-3 text-sm text-yellow-400 hover:text-yellow-300 hover:bg-gray-700 dark:hover:bg-gray-800">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Painel Admin
                        </a>
                        @endif

                        <div class="border-t border-gray-700 my-2"></div>

                        <form method="POST" action="{{ route('logout') }}" class="px-4">
                            @csrf
                            <button type="submit" class="flex items-center w-full text-left px-0 py-3 text-sm text-gray-300 dark:text-gray-400 hover:text-white dark:hover:text-gray-200">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Sair
                            </button>
                        </form>
                        @endauth

                        @guest
                        <div class="px-4 py-2 border-b border-gray-700">
                            <h3 class="text-sm font-medium text-gray-300 dark:text-gray-400">Conta</h3>
                        </div>

                        <a href="{{ route('games.free') }}" class="flex items-center px-4 py-3 text-sm text-gray-300 dark:text-gray-400 hover:text-white dark:hover:text-gray-200 hover:bg-gray-700 dark:hover:bg-gray-800">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                            Jogos Gratuitos
                        </a>

                        <a href="{{ route('login') }}" class="flex items-center px-4 py-3 text-sm text-gray-300 dark:text-gray-400 hover:text-white dark:hover:text-gray-200 hover:bg-gray-700 dark:hover:bg-gray-800">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Login
                        </a>

                        <a href="{{ route('register') }}" class="flex items-center px-4 py-3 text-sm text-gray-300 dark:text-gray-400 hover:text-white dark:hover:text-gray-200 hover:bg-gray-700 dark:hover:bg-gray-800">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                            Registrar
                        </a>
                        @endguest
                    </div>
                </div>
            </div>
            </div> <!-- Fecha Container da direita -->
        </div>
    </div>

    <style>
        .custom-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scroll::-webkit-scrollbar-thumb {
            background-color: rgba(100, 100, 100, 0.4);
            border-radius: 3px;
        }

        /* Forçar visibilidade correta dos menus */
        @media (max-width: 1023px) {
            /* Mobile: mostrar menu hamburger, esconder menu do usuário */
            #mobile-menu-container {
                display: flex !important;
            }
        }
        
        @media (min-width: 1024px) {
            /* Desktop: esconder menu hamburger, mostrar menu do usuário */
            #mobile-menu-container {
                display: none !important;
            }
            
            /* Garantir que o menu do usuário apareça em desktop */
            #desktop-user-menu {
                display: flex !important;
            }
        }
    </style>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    try {
        if (window.LARAVEL_DEBUG) console.log('🔧 Inicializando sistema de dropdowns...');
        
        // Sistema de dropdowns com JavaScript vanilla
        const dropdowns = {
            userMenu: {
                button: document.querySelector('[data-dropdown="userMenu"]'),
                content: document.querySelector('[data-dropdown-content="userMenu"]'),
                isOpen: false
            }
        };
        
        // Função para fechar todos os dropdowns
        function closeAllDropdowns() {
            if (window.LARAVEL_DEBUG) console.log('🔒 Fechando todos os dropdowns');
            Object.keys(dropdowns).forEach(key => {
                const dropdown = dropdowns[key];
                if (dropdown.content) {
                    dropdown.content.classList.add('hidden');
                    dropdown.isOpen = false;
                }
            });
        }
        
        // Função para abrir dropdown específico
        function openDropdown(name) {
            if (window.LARAVEL_DEBUG) console.log(`🔓 Abrindo dropdown: ${name}`);
            closeAllDropdowns(); // Fechar outros primeiro
            
            const dropdown = dropdowns[name];
            if (dropdown && dropdown.content) {
                dropdown.content.classList.remove('hidden');
                dropdown.isOpen = true;
            }
        }
        
        // Função para toggle dropdown
        function toggleDropdown(name) {
            const dropdown = dropdowns[name];
            if (dropdown && dropdown.content) {
                if (dropdown.isOpen) {
                    if (window.LARAVEL_DEBUG) console.log(`🔒 Fechando dropdown: ${name}`);
                    dropdown.content.classList.add('hidden');
                    dropdown.isOpen = false;
                } else {
                    openDropdown(name);
                }
            }
        }
        
        // Adicionar listeners nos botões
        Object.keys(dropdowns).forEach(key => {
            const dropdown = dropdowns[key];
            if (dropdown.button) {
                dropdown.button.addEventListener('click', (e) => {
                    e.stopPropagation();
                    e.preventDefault();
                    toggleDropdown(key);
                });
            }
        });
        
        // Fechar ao clicar fora
        document.addEventListener('click', (e) => {
            let clickedInsideDropdown = false;
            
            Object.keys(dropdowns).forEach(key => {
                const dropdown = dropdowns[key];
                if (dropdown.button && dropdown.content) {
                    if (dropdown.button.contains(e.target) || dropdown.content.contains(e.target)) {
                        clickedInsideDropdown = true;
                    }
                }
            });
            
            if (!clickedInsideDropdown) {
                closeAllDropdowns();
            }
        });
        
        // Fechar com ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeAllDropdowns();
            }
        });
        
        // Fechar ao clicar em links dentro dos dropdowns
        document.querySelectorAll('[data-dropdown-content] a, [data-dropdown-content] button[type="submit"]').forEach(element => {
            element.addEventListener('click', () => {
                setTimeout(closeAllDropdowns, 100);
            });
        });
        
        if (window.LARAVEL_DEBUG) console.log('✅ Sistema de dropdowns inicializado');
    } catch (error) {
        console.error('❌ Erro ao inicializar dropdowns:', error);
    }
    
    // ========== DARK MODE SYSTEM ==========
    try {
        if (window.LARAVEL_DEBUG) console.log('🌙 Inicializando sistema de dark mode...');
        
        const darkModeToggle = document.getElementById('dark-mode-toggle');
        const darkModeToggleMobile = document.getElementById('dark-mode-toggle-mobile');
        const htmlElement = document.documentElement;
        
        // Função para atualizar ícones
        function updateDarkModeIcons(isDark) {
            const sunIcons = document.querySelectorAll('.sun-icon');
            const moonIcons = document.querySelectorAll('.moon-icon');
            
            sunIcons.forEach(icon => {
                icon.classList.toggle('hidden', isDark);
            });
            
            moonIcons.forEach(icon => {
                icon.classList.toggle('hidden', !isDark);
            });
        }
        
        // Função para aplicar dark mode
        function applyDarkMode(isDark) {
            if (isDark) {
                htmlElement.classList.add('dark');
                localStorage.setItem('darkMode', 'true');
            } else {
                htmlElement.classList.remove('dark');
                localStorage.setItem('darkMode', 'false');
            }
            updateDarkModeIcons(isDark);
            if (window.LARAVEL_DEBUG) console.log('🌙 Dark mode:', isDark ? 'ativado' : 'desativado');
        }
        
        // Função para toggle dark mode
        function toggleDarkMode() {
            const isDark = !htmlElement.classList.contains('dark');
            applyDarkMode(isDark);
        }
        
        // Verificar preferência salva ou preferência do sistema
        const savedDarkMode = localStorage.getItem('darkMode');
        const systemDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        let initialDarkMode;
        if (savedDarkMode !== null) {
            initialDarkMode = savedDarkMode === 'true';
        } else {
            initialDarkMode = systemDarkMode;
        }
        
        // Aplicar configuração inicial
        applyDarkMode(initialDarkMode);
        
        // Event listeners para os botões
        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', toggleDarkMode);
        }
        
        if (darkModeToggleMobile) {
            darkModeToggleMobile.addEventListener('click', toggleDarkMode);
        }

        // Sidebar toggle mobile - Temporariamente desabilitado para simplificar
        const sidebarToggleMobile = document.getElementById('sidebar-toggle-mobile');
        if (sidebarToggleMobile) {
            sidebarToggleMobile.style.display = 'none'; // Ocultar botão por enquanto
        }

        // Escutar mudanças na preferência do sistema
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (localStorage.getItem('darkMode') === null) {
                applyDarkMode(e.matches);
            }
        });
        
        if (window.LARAVEL_DEBUG) console.log('✅ Sistema de dark mode inicializado');
    } catch (error) {
        console.error('❌ Erro ao inicializar dark mode:', error);
    }
});
</script>

@auth
<script>
// 📢 Função para enviar notificação global para todos os usuários
function sendGlobalNotification() {
    const messages = [
        // � Wishlist & Interesse Pessoal
        '🎯 WISHLIST ALERT! O jogo da sua lista está com 70% OFF!',
        '💝 Presente perfeito! Jogo da sua wishlist em promoção especial',
        '🔔 Notificação personalizada: Seu jogo favorito está mais barato!',
        '⭐ O jogo que você estava esperando finalmente entrou em oferta!',
        
        // 🔥 Jogos Famosos & AAA
        '🔥 BLOCKBUSTER EM OFERTA! Cyberpunk 2077 com 80% OFF',
        '⚡ RED DEAD REDEMPTION 2 por apenas R$ 59,90!',
        '🎮 GRAND THEFT AUTO V com 75% de desconto!',
        '🏆 THE WITCHER 3 GOTY por preço histórico!',
        '🚀 CALL OF DUTY com mega desconto por tempo limitado!',
        
        // ⚡ Flash Sales & Ofertas Relâmpago
        '⚡ FLASH SALE! 6 horas restantes - 90% OFF!',
        '🧨 BOOM! Explosão de descontos por 24h apenas!',
        '⏰ ÚLTIMA HORA! Oferta expira em 2 horas!',
        '💥 BREAKING: Mega promoção surprise por tempo limitado!',
        '🚨 ALERTA VERMELHO! Preços nunca vistos - corre!',
        
        // 💎 Indies & Jogos Especiais
        '💎 INDIE GEMS! Pérolas escondidas com até 95% OFF',
        '🌟 DESCOBERTA INCRÍVEL! Jogo indie premiado em oferta',
        '🎨 ARTE INTERATIVA! Jogos únicos com preços especiais',
        '🧩 PUZZLE PARADISE! Quebra-cabeças incríveis em promoção',
        
        // 🏷️ Categorias Específicas
        '�️ VELOCIDADE MÁXIMA! Jogos de corrida com 80% OFF',
        '⚔️ BATALHAS ÉPICAS! RPGs medievais em promoção',
        '🚀 ESPAÇO SIDERAL! Jogos sci-fi com descontos galácticos',
        '🧟 SURVIVAL HORROR! Sustos garantidos com preços baixos',
        
        // 💰 Ofertas Especiais & Bundles
        '📦 MEGA BUNDLE! 10 jogos por R$ 49,90 - Economia de 95%!',
        '💰 PACOTÃO PREMIUM! Coleção completa com desconto histórico',
        '🎮 GAME PASS KILLER! Bundle melhor que assinatura',
        '🏆 GOLD EDITION! Versão completa com 80% de desconto',
        
        // 🎊 Eventos & Sazonais
        '🎊 WEEKEND MADNESS! Fim de semana de ofertas insanas',
        '🎈 ANIVERSÁRIO STEAM! Celebre com 85% de desconto',
        '🎄 NATAL CHEGOU CEDO! Presentes com super descontos',
        '☀️ SUMMER SALE! Férias com jogos em promoção',
        
        // 🏅 Premiados & Aclamados
        '🏅 GAME OF THE YEAR! Vencedor do GOTY com 75% OFF',
        '⭐ 10/10 IGN! Jogo perfeito com preço imperfeito',
        '🏆 CRITICS CHOICE! 98% no Metacritic em oferta',
        '👑 PLAYERS FAVORITE! Eleito pelos gamers, aprovado no preço'
    ];
    
    const randomMessage = messages[Math.floor(Math.random() * messages.length)];
    
    console.log('📢 Enviando notificação global para todos os usuários...');
    
    fetch('/notifications/broadcast-web', {
        method: 'POST',
        credentials: 'same-origin', // Importante para incluir cookies de sessão
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify({
            message: randomMessage
        })
    })
    .then(response => {
        console.log('📡 Status da notificação global:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('📢 Resultado da notificação global:', data);
        
        if (data.success) {
            const stats = data.stats;
            console.log(`✅ Notificação enviada com sucesso!`);
            console.log(`👥 Total de usuários: ${stats.total_users}`);
            console.log(`✅ Sucesso: ${stats.success_count}`);
            console.log(`❌ Erros: ${stats.error_count}`);
            console.log(`💬 Mensagem: ${data.message}`);
            
            // Mostrar toast de confirmação
            if (window.showToast) {
                window.showToast({
                    message: `📢 Notificação enviada para ${stats.success_count} usuário(s)! 💬 "${data.message}"`,
                    type: 'success'
                });
            }
            
            // Tentar reproduzir som de sucesso
            if (window.playNotificationSound) {
                window.playNotificationSound();
            }
            
        } else {
            console.log('❌ Falha ao enviar notificação:', data.message);
            
            if (window.showToast) {
                window.showToast({
                    message: `❌ Erro ao enviar notificação: ${data.message}`,
                    type: 'error'
                });
            }
        }
    })
    .catch(error => {
        console.error('❌ Erro ao enviar notificação global:', error);
        
        if (window.showToast) {
            window.showToast({
                message: '❌ Erro de conexão ao enviar notificação global',
                type: 'error'
            });
        }
    });
}
</script>
@endauth
