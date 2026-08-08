<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $seoTitle = trim($__env->yieldContent('title')) ?: config('app.name', 'DealHunt');
        $seoDescription = trim($__env->yieldContent('meta_description'))
            ?: 'Ofertas de jogos em tempo real — Steam, Epic e mais lojas. Wishlist e alertas no DealHunt.';
        $seoCanonical = trim($__env->yieldContent('canonical')) ?: url()->current();
        $seoImage = trim($__env->yieldContent('og_image')) ?: asset('favicon.ico');
        $seoRobots = trim($__env->yieldContent('meta_robots')) ?: 'index,follow';
    @endphp
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($seoDescription), 160) }}">
    <meta name="robots" content="{{ $seoRobots }}">
    <link rel="canonical" href="{{ $seoCanonical }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="{{ config('app.name', 'DealHunt') }}">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($seoDescription), 160) }}">
    <meta property="og:url" content="{{ $seoCanonical }}">
    <meta property="og:image" content="{{ $seoImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($seoDescription), 160) }}">
    <meta name="twitter:image" content="{{ $seoImage }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;700&display=swap" rel="stylesheet" />
    <!-- Toastify CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <style>
        :root {
            --color-primary: #b91c1c;
            /* vermelho do tema */
            
            /* Tema padrão (vermelho escuro) */
            --bg-primary: #1f2937;
            --bg-secondary: #374151;
            --bg-tertiary: #4b5563;
            --text-primary: #f9fafb;
            --text-secondary: #e5e7eb;
            --text-muted: #9ca3af;
            --border-color: #6b7280;
            --shadow-color: rgba(0, 0, 0, 0.3);
        }

        .dark {
            /* Dark mode (ainda mais escuro) */
            --bg-primary: #000000;
            --bg-secondary: #0f172a;
            --bg-tertiary: #1e293b;
            --text-primary: #ffffff;
            --text-secondary: #f1f5f9;
            --text-muted: #cbd5e1;
            --border-color: #334155;
            --shadow-color: rgba(0, 0, 0, 0.8);
        }

        body {
            overflow-x: hidden;
            font-family: 'Montserrat', 'Figtree', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .app-title {
            font-family: 'Montserrat', 'Figtree', sans-serif;
            letter-spacing: 1px;
        }

        @keyframes slide-in {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-in {
            animation: slide-in 0.3s ease-out;
        }

        /* Estilos para as notificações individuais */
        .notification-toast {
            background: linear-gradient(135deg, #b91c1c 0%, #dc2626 100%);
            border: 1px solid rgba(220, 38, 38, 0.3);
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            max-width: 320px;
            min-width: 280px;
            max-height: 150px; /* Limita altura máxima */
            word-break: break-word;
            border-radius: 0.75rem;
            pointer-events: auto;
            transform: translateX(100%);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            overflow: hidden; /* Evita barras de rolagem */
            box-sizing: border-box; /* Inclui padding e border no tamanho total */
            position: relative; /* Para posicionamento correto */
        }

        .notification-toast.show {
            transform: translateX(0);
        }

        .notification-toast.hide {
            transform: translateX(100%) translateY(-20px);
            opacity: 0;
        }

        /* Dark mode para notificações */
        .dark .notification-toast {
            background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 100%);
            border: 1px solid rgba(153, 27, 27, 0.3);
        }

        /* Evitar overflow global causado por toasts */
        #toast-container {
            overflow: visible !important;
        }
        
        #toast-container .notification-toast {
            text-overflow: ellipsis;
            white-space: normal;
        }

        /* Garantir que conteúdo longo seja quebrado corretamente */
        .notification-toast p {
            hyphens: auto;
            word-break: break-word;
            overflow-wrap: break-word;
        }

        /* Botões da notificação */
        .notification-btn {
            transition: all 0.2s ease;
            border-radius: 0.375rem;
        }

        .notification-btn:hover {
            transform: scale(1.05);
        }

        /* Container das notificações - sem estilos visuais conflitantes */
        #toast-container {
            position: fixed;
            bottom: 1.25rem;
            right: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            z-index: 9999;
            pointer-events: none;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        /* Debug styles para garantir que o conteúdo apareça */
        main {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            z-index: 1 !important;
        }
        
        /* Remover espaços desnecessários */
        nav {
            margin-bottom: 0 !important;
        }
        
        main {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }
        
        /* Ajustes para navbar fixo */
        body {
            padding-top: 64px; /* altura do navbar */
        }
        
        .sidebar-fixed {
            top: 64px !important; /* ajustar para baixo do navbar */
            height: calc(100vh - 64px) !important; /* altura total menos navbar */
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased min-h-screen bg-gradient-to-br from-gray-900 via-red-900 to-red-800 dark:from-black dark:via-gray-900 dark:to-black text-white transition-colors duration-300">
    <div class="min-h-screen flex flex-col overflow-x-hidden">
        @include('layouts.navigation')
        
        <div class="flex flex-1 overflow-hidden">
            <!-- Menu Lateral Fixo -->
            @auth
            <aside class="w-64 bg-gray-800/50 dark:bg-gray-900/70 backdrop-blur-sm border-r border-gray-700/50 dark:border-gray-600/50 flex-shrink-0 fixed left-0 z-10 overflow-y-auto transition-colors duration-300 sidebar-fixed">
                @include('layouts.sidebar')
            </aside>
            @endauth
            
            <!-- Conteúdo Principal -->
            <main class="flex-1 @auth ml-64 @endauth overflow-x-hidden">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Toastify JS -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <!-- Notification Sound -->
    <script src="{{ asset('js/notification-sound.js') }}"></script>

    @auth
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🔔 Inicializando sistema de notificações para usuário {{ auth()->id() }}...');
            
            // Sistema de controle de silenciamento
            let isSilenced = false;
            let silenceUntil = null;
            
            // Verificar se está silenciado
            const checkSilence = () => {
                const silenceData = localStorage.getItem('notificationSilence');
                if (silenceData) {
                    const data = JSON.parse(silenceData);
                    if (Date.now() < data.until) {
                        isSilenced = true;
                        silenceUntil = data.until;
                        return true;
                    } else {
                        localStorage.removeItem('notificationSilence');
                        isSilenced = false;
                        silenceUntil = null;
                    }
                }
                return false;
            };
            
            // Silenciar por tempo específico
            const silenceNotifications = (minutes) => {
                const until = Date.now() + (minutes * 60 * 1000);
                localStorage.setItem('notificationSilence', JSON.stringify({ until }));
                isSilenced = true;
                silenceUntil = until;
                console.log(`Notificações silenciadas por ${minutes} minutos`);
            };
            
            // Criar ou obter o container de toasts
            let toastContainer = document.getElementById('toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toast-container';
                toastContainer.className = 'fixed bottom-5 right-5 flex flex-col gap-3 z-[9999] pointer-events-none max-w-xs sm:max-w-sm overflow-hidden';
                toastContainer.style.maxHeight = '80vh'; // Limita altura máxima
                toastContainer.style.overflowY = 'visible'; // Evita scroll interno
                document.body.appendChild(toastContainer);
            }
            
            // Controle de notificações já exibidas (persistente durante a sessão)
            const sessionKey = 'shownNotifications_{{ auth()->id() }}';
            let shownToastIds = new Set();
            
            // Recuperar IDs já mostrados nesta sessão
            try {
                const stored = sessionStorage.getItem(sessionKey);
                if (stored) {
                    shownToastIds = new Set(JSON.parse(stored));
                }
            } catch (e) {
                console.warn('Erro ao recuperar notificações da sessão:', e);
            }
            
            // Salvar IDs no sessionStorage
            function saveShownIds() {
                try {
                    sessionStorage.setItem(sessionKey, JSON.stringify([...shownToastIds]));
                } catch (e) {
                    console.warn('Erro ao salvar notificações na sessão:', e);
                }
            }

            // Tocar som de notificação
            function playNotificationSound() {
                if (isSilenced) return;
                
                // Primeiro tentar Web Audio API para garantir que funcione
                try {
                    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                    const oscillator = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();
                    
                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);
                    
                    // Som de notificação discreto
                    oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
                    oscillator.type = 'sine';
                    
                    // Envelope de volume para som suave
                    gainNode.gain.setValueAtTime(0, audioContext.currentTime);
                    gainNode.gain.linearRampToValueAtTime(0.1, audioContext.currentTime + 0.01);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);
                    
                    oscillator.start(audioContext.currentTime);
                    oscillator.stop(audioContext.currentTime + 0.2);
                    
                    console.log('🔊 Som de notificação reproduzido via Web Audio API');
                } catch (err) {
                    // Fallback para arquivo MP3
                    try {
                        const audio = new Audio('/sounds/notify.mp3');
                        audio.volume = 0.3;
                        audio.play().then(() => {
                            console.log('🔊 Som de notificação reproduzido via MP3');
                        }).catch(() => {
                            console.log('⚠️ Não foi possível reproduzir o som');
                        });
                    } catch (audioErr) {
                        console.log('⚠️ Nenhum método de áudio funcionou');
                    }
                }
            }

            function showToast(notification) {
                // Verificar se já foi mostrada nesta sessão
                if (shownToastIds.has(notification.id)) {
                    console.log('Notificação já exibida nesta sessão:', notification.id);
                    return;
                }
                
                // Verificar se está silenciado
                if (checkSilence()) {
                    console.log('Notificações silenciadas até:', new Date(silenceUntil));
                    return;
                }
                
                // Adicionar ao controle de exibidas
                shownToastIds.add(notification.id);
                saveShownIds();

                const toast = document.createElement('div');
                toast.className = 'notification-toast text-white px-4 py-3 rounded-xl shadow-lg cursor-pointer hover:scale-105 transition-all duration-200 pointer-events-auto';
                
                // Garantir que não há overflow
                toast.style.overflow = 'hidden';
                toast.style.boxSizing = 'border-box';
                toast.style.maxWidth = '320px';
                toast.style.minWidth = '280px';
                
                const closeToast = () => {
                    if (toast.parentElement) {
                        toast.classList.add('hide');
                        setTimeout(() => {
                            if (toast.parentElement) {
                                toast.remove();
                            }
                        }, 300);
                    }
                };

                toast.innerHTML = `
                    <div class="flex items-start justify-between gap-3" style="overflow: hidden;">
                        <div class="flex-1" style="overflow: hidden;">
                            <p class="text-sm font-medium break-words" style="word-wrap: break-word; overflow-wrap: break-word;">${notification.message}</p>
                            ${notification.game ? `<p class="text-xs text-red-200 mt-1 break-words" style="word-wrap: break-word;">${notification.game.title}</p>` : ''}
                        </div>
                        <div class="flex gap-1 ml-2">
                            <button type="button" class="notification-btn silence-btn text-red-200 hover:text-white text-xs px-2 py-1 bg-red-700/30 hover:bg-red-600/50 rounded" title="Silenciar por 1h">
                                🔕
                            </button>
                            <button type="button" class="notification-btn close-btn text-red-200 hover:text-white text-lg leading-none w-6 h-6 flex items-center justify-center hover:bg-red-600/30 rounded">×</button>
                        </div>
                    </div>
                `;

                // Eventos dos botões
                const closeButton = toast.querySelector('.close-btn');
                const silenceButton = toast.querySelector('.silence-btn');
                
                closeButton.addEventListener('click', (e) => {
                    e.stopPropagation();
                    closeToast();
                });
                
                silenceButton.addEventListener('click', (e) => {
                    e.stopPropagation();
                    silenceNotifications(60); // 1 hora
                    closeToast();
                    
                    // Mostrar feedback visual
                    const feedbackToast = document.createElement('div');
                    feedbackToast.className = 'notification-toast text-white px-4 py-2 rounded-xl shadow-lg pointer-events-auto bg-gray-700';
                    feedbackToast.innerHTML = '<p class="text-sm">🔕 Notificações silenciadas por 1 hora</p>';
                    toastContainer.appendChild(feedbackToast);
                    
                    setTimeout(() => {
                        feedbackToast.classList.add('show');
                    }, 50);
                    
                    setTimeout(() => {
                        feedbackToast.classList.add('hide');
                        setTimeout(() => feedbackToast.remove(), 300);
                    }, 3000);
                });

                // Adicionar ao container
                toastContainer.appendChild(toast);
                
                // Animação de entrada
                setTimeout(() => {
                    toast.classList.add('show');
                }, 50);

                // Auto remove after 8 seconds (aumentado para dar tempo de ler)
                const autoCloseTimeout = setTimeout(closeToast, 8000);

                // Mark as read when clicked (exceto nos botões)
                toast.addEventListener('click', function(e) {
                    if (!e.target.closest('.notification-btn')) {
                        if (notification.id) {
                            fetch(`/api/notifications/${notification.id}/mark-as-read`, {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                }
                            }).catch(err => console.warn('Erro ao marcar como lida:', err));
                        }
                        clearTimeout(autoCloseTimeout);
                        closeToast();
                    }
                });
                
                // Tocar som
                playNotificationSound();
                
                console.log('✅ Nova notificação exibida:', notification.message);
            }

            // Configurar Laravel Echo para notificações em tempo real
            let isEchoConnected = false;
            let lastNotificationCheck = Date.now();
            
            // Tentativa de configurar Echo/WebSocket
            if (window.Echo) {
                console.log('🔌 Tentando conectar Echo...');
                
                try {
                    const channel = window.Echo.private(`user.{{ auth()->id() }}`);
                    
                    channel.listen('.notification.new', (e) => {
                        console.log('📨 Nova notificação via WebSocket:', e);
                        // O som será tocado automaticamente dentro de showToast()
                        showToast(e);
                        updateNotificationDropdown();
                    });
                    
                    // Verificar conexão
                    setTimeout(() => {
                        if (window.Echo.connector && window.Echo.connector.socket.connected) {
                            isEchoConnected = true;
                            console.log('✅ Echo conectado com sucesso!');
                        } else {
                            console.log('⚠️ Echo não conectou, usando polling');
                        }
                    }, 2000);
                    
                } catch (error) {
                    console.log('❌ Erro no Echo, usando polling:', error);
                }
            } else {
                console.log('⚠️ Echo não disponível, usando polling');
            }
            
            // Sistema de polling agressivo como principal método
            console.log('🔄 Iniciando sistema de polling para notificações...');
            
            let pollingInterval;
            
            function startPolling() {
                // Polling a cada 5 segundos (mais razoável)
                pollingInterval = setInterval(() => {
                    if (!checkSilence()) {
                        checkNotifications();
                    }
                }, 5000);
                
                // Verificação imediata
                checkNotifications();
            }
            
            function stopPolling() {
                if (pollingInterval) {
                    clearInterval(pollingInterval);
                }
            }
            
            // Função para verificar notificações com melhor controle
            function checkNotifications() {
                const now = Date.now();
                
                // Não fazer polling se estiver silenciado
                if (checkSilence()) {
                    console.log('🔕 Polling pausado - notificações silenciadas');
                    return;
                }
                
                // Evitar muitas requisições simultâneas
                if (now - lastNotificationCheck < 2000) {
                    return;
                }
                
                lastNotificationCheck = now;
                console.log('🔍 Verificando novas notificações...');
                
                fetch('/notifications/check-new-web', {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.hasNew && data.notifications) {
                        console.log('🎉 Novas notificações encontradas:', data.notifications.length);
                        
                        data.notifications.forEach(notification => {
                            console.log('📨 Mostrando notificação:', notification);
                            // O som será tocado automaticamente dentro de showToast()
                            showToast(notification);
                        });
                    } else if (data.success && !data.hasNew) {
                        console.log('👍 Nenhuma nova notificação');
                    }
                })
                .catch(error => {
                    console.error('❌ Erro ao verificar notificações:', error);
                });
            }
            
            // Verificação inicial e início do polling
            console.log('🚀 Iniciando sistema de notificações...');
            checkNotifications();
            startPolling();
            
            // Funções globais para controle
            window.silenceNotifications = silenceNotifications;
            window.unsilenceNotifications = () => {
                localStorage.removeItem('notificationSilence');
                isSilenced = false;
                silenceUntil = null;
                console.log('Notificações reativadas');
            };
            window.checkNotifications = checkNotifications;
            window.showToast = showToast; // Tornar showToast globalmente acessível
            window.playNotificationSound = playNotificationSound; // Tornar função de som acessível
        });

        
        // Função global para limpar todas as notificações (para debug)
        window.clearAllNotifications = function() {
            const container = document.getElementById('toast-container');
            if (container) {
                container.innerHTML = '';
            }
            const allToasts = document.querySelectorAll('.notification-toast, .toastify');
            allToasts.forEach(toast => toast.remove());
            console.log('Todas as notificações foram removidas');
        };
        
        // Função para resetar o controle de notificações (para debug)
        window.resetNotificationControl = function() {
            const sessionKey = 'shownNotifications_{{ auth()->id() }}';
            sessionStorage.removeItem(sessionKey);
            console.log('Controle de notificações resetado - notificações poderão aparecer novamente');
        };
    </script>
    @endauth
    
    <!-- Modal de Login Personalizado -->
    <div id="loginModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeLoginModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Login Necessário</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Para acessar este recurso, você precisa estar logado em sua conta.
                                </p>
                                <p class="text-sm text-gray-500 mt-2">
                                    Se você ainda não tem uma conta, pode criar uma gratuitamente!
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <a href="{{ route('login') }}" 
                       class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Fazer Login
                    </a>
                    <a href="{{ route('register') }}" 
                       class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Criar Conta
                    </a>
                    <button type="button" onclick="closeLoginModal()" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    {{-- JavaScript para modal de login --}}
    <script>
        function showLoginPrompt() {
            document.getElementById('loginModal').classList.remove('hidden');
        }
        
        function closeLoginModal() {
            document.getElementById('loginModal').classList.add('hidden');
        }
        
        // Fechar modal ao pressionar Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLoginModal();
            }
        });
    </script>
    
    {{-- Wishlist AJAX --}}
    <script src="{{ asset('js/wishlist-ajax.js') }}"></script>
    
    {{-- Alpine.js para interatividade --}}
    <script src="//unpkg.com/alpinejs" defer></script>
</body>
</html>