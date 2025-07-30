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
        <div class="flex justify-between h-16">
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

            <!-- Menu de navegação pública -->
            <div class="hidden sm:flex sm:items-center sm:space-x-8">
                <a href="{{ route('games.free') }}" class="text-gray-300 dark:text-gray-400 hover:text-white dark:hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                    🎮 Jogos Gratuitos
                </a>
            </div>

            <!-- Menu do usuário -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-4">
                @auth
                    <!--  Notificação Global (Admin) -->
                    <button 
                        onclick="sendGlobalNotification()"
                        class="text-gray-300 dark:text-gray-400 hover:text-white dark:hover:text-gray-200 transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-purple-400 dark:focus:ring-gray-500 focus:ring-opacity-50 rounded-lg p-2"
                        title="Enviar notificação para todos os usuários">
                        📢
                    </button>
                @endauth
                
                <!-- 🌙 Toggle Dark Mode -->
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
                
                @auth
                <!-- 🔔 Dropdown Notificações -->
                <div class="relative">
                    <button 
                        data-dropdown="notifications"
                        class="relative text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 focus:outline-none">
                        🔔
                        @if ($unreadCount > 0)
                        <span
                            class="absolute -top-1 -right-2 bg-red-600 dark:bg-red-500 text-white text-xs font-bold rounded-full px-1 py-0.5">{{ $unreadCount }}</span>
                        @endif
                    </button>

                    <div 
                        data-dropdown-content="notifications"
                        class="hidden absolute right-0 mt-2 z-50 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg overflow-hidden transition-all duration-200"
                        style="width: 22rem; max-width: 90vw; box-sizing: border-box;">

                        <div class="px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 border-b dark:border-gray-600 flex justify-between items-center">
                            <span>Notificações</span>
                            <button 
                                data-close-dropdown="notifications"
                                class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 text-lg font-bold leading-none"
                                title="Fechar">
                                ×
                            </button>
                        </div>

                        <div class="max-h-80 overflow-y-auto px-4 py-3 space-y-4 custom-scroll">
                            @forelse ($recentNotifications as $group => $items)
                            <div class="text-xs font-bold text-gray-500 dark:text-gray-400">{{ $group }}</div>

                            @foreach ($items as $not)
                            <div
                                class="bg-gray-50 dark:bg-gray-700 text-sm px-4 py-3 rounded-lg shadow-sm flex items-start gap-3 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors {{ $not->read ? 'text-gray-400' : 'text-gray-900 dark:text-white' }}">
                                <div class="text-xl mt-0.5">
                                    @php
                                    $icon = Str::contains($not->message, ['desconto', 'promo']) ? '💲' : '🔔';
                                    @endphp
                                    {!! $icon !!}
                                </div>
                                <div class="flex-1 leading-relaxed">
                                    {{ $not->message }}
                                    <div class="text-xs text-right text-gray-500 dark:text-gray-300 mt-1">
                                        {{ $not->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            @empty
                            <div class="text-sm text-gray-500 dark:text-gray-300 px-4">Sem notificações recentes.</div>
                            @endforelse
                        </div>

                        <div class="px-4 py-2 border-t dark:border-gray-600 flex items-center justify-between text-xs">
                            <form action="{{ route('notifications.markAll') }}" method="POST" class="inline">
                                @csrf
                                <button 
                                    type="submit" 
                                    class="text-red-600 dark:text-red-400 hover:underline">
                                    Marcar todas como lidas
                                </button>
                            </form>
                            <a 
                                href="{{ route('notifications.index') }}" 
                                class="text-gray-600 dark:text-gray-400 hover:underline">
                                Ver todas
                            </a>
                        </div>
                    </div>
                </div>
                @endauth

                <!-- 🔓 Menu do usuário -->
                <div class="relative">
                    <button 
                        data-dropdown="userMenu"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none transition ease-in-out duration-150">
                        @auth
                        <img src="{{ asset(Auth::user()->profile_photo_url ?? 'images/default-avatar.png') }}"
                             alt="Foto de perfil"
                             class="w-8 h-8 rounded-full object-cover border border-gray-300 dark:border-gray-600 shadow mr-2" />
                        @endauth
                        <div>{{ Auth::check() ? Auth::user()->name : 'Entrar' }}</div>
                        <div class="ms-1">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </button>

                    <div 
                        data-dropdown-content="userMenu"
                        class="hidden absolute right-0 z-50 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 dark:ring-gray-600 transition-all duration-200">
                        <div class="py-1">
                            @auth
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Perfil</a>
                            <a href="{{ route('wishlist.index') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Lista de Interesse</a>
                            <a href="{{ route('notifications.index') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Notificações</a>
                            
                            <!-- Link para Painel Administrativo (apenas para admins) -->
                            @if(auth()->user()->is_admin)
                                <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>
                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    🛠️ Painel Administrativo
                                </a>
                            @endif

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    Sair
                                </button>
                            </form>
                            @else
                            <a href="{{ route('login') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Entrar</a>
                            <a href="{{ route('login', ['register' => 1]) }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Criar conta</a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>

            <!-- Menu mobile -->
            <div class="sm:hidden flex items-center gap-2">
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
                
                <!-- Hamburger menu -->
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 focus:text-gray-500 dark:focus:text-gray-300 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1 bg-gray-800 dark:bg-gray-900">
            <!-- Link público para jogos gratuitos -->
            <a href="{{ route('games.free') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 dark:text-gray-400 hover:text-white dark:hover:text-gray-200 hover:bg-gray-700 dark:hover:bg-gray-800">
                🎮 Jogos Gratuitos
            </a>
            
            @auth
            <a href="{{ route('wishlist.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 dark:text-gray-400 hover:text-white dark:hover:text-gray-200 hover:bg-gray-700 dark:hover:bg-gray-800">
                Minha Wishlist
            </a>
            <a href="{{ route('notifications.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 dark:text-gray-400 hover:text-white dark:hover:text-gray-200 hover:bg-gray-700 dark:hover:bg-gray-800">
                Notificações
                @if ($unreadCount > 0)
                <span class="ml-2 bg-red-600 dark:bg-red-500 text-white text-xs font-bold rounded-full px-2 py-1">{{ $unreadCount }}</span>
                @endif
            </a>
            @endauth
            
            @guest
            <a href="{{ route('login') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 dark:text-gray-400 hover:text-white dark:hover:text-gray-200 hover:bg-gray-700 dark:hover:bg-gray-800">
                Login
            </a>
            <a href="{{ route('register') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 dark:text-gray-400 hover:text-white dark:hover:text-gray-200 hover:bg-gray-700 dark:hover:bg-gray-800">
                Registrar
            </a>
            @endguest
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
    </style>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    try {
        console.log('🔧 Inicializando sistema de dropdowns...');
        
        // Sistema de dropdowns com JavaScript vanilla
        const dropdowns = {
            notifications: {
                button: document.querySelector('[data-dropdown="notifications"]'),
                content: document.querySelector('[data-dropdown-content="notifications"]'),
                isOpen: false
            },
            userMenu: {
                button: document.querySelector('[data-dropdown="userMenu"]'),
                content: document.querySelector('[data-dropdown-content="userMenu"]'),
                isOpen: false
            }
        };
        
        // Função para fechar todos os dropdowns
        function closeAllDropdowns() {
            console.log('🔒 Fechando todos os dropdowns');
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
            console.log(`🔓 Abrindo dropdown: ${name}`);
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
                    console.log(`🔒 Fechando dropdown: ${name}`);
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
        
        // Botão de fechar notificações
        const closeNotificationBtn = document.querySelector('[data-close-dropdown="notifications"]');
        if (closeNotificationBtn) {
            closeNotificationBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                e.preventDefault();
                closeAllDropdowns();
            });
        }
        
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
        
        console.log('✅ Sistema de dropdowns inicializado');
    } catch (error) {
        console.error('❌ Erro ao inicializar dropdowns:', error);
    }
    
    // ========== DARK MODE SYSTEM ==========
    try {
        console.log('🌙 Inicializando sistema de dark mode...');
        
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
            console.log('🌙 Dark mode:', isDark ? 'ativado' : 'desativado');
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
        
        // Escutar mudanças na preferência do sistema
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (localStorage.getItem('darkMode') === null) {
                applyDarkMode(e.matches);
            }
        });
        
        console.log('✅ Sistema de dark mode inicializado');
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
