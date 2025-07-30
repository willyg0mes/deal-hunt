import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Verificar se as variáveis do Pusher estão configuradas antes de inicializar
const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY;

if (pusherKey && pusherKey !== 'local' && pusherKey !== '') {
    // Configuração para usar com Pusher (produção)
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: pusherKey,
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
        wsHost: import.meta.env.VITE_PUSHER_HOST ?? `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1'}.pusherapp.com`,
        wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
        wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
        auth: {
            headers: {
                Authorization: `Bearer ${window.localStorage.getItem('auth_token')}`,
            },
        },
    });
} else {
    // Para desenvolvimento local ou quando Pusher não está configurado, criar um Echo mock
    console.log('Pusher não configurado - usando Echo mock para desenvolvimento');
    window.Echo = {
        channel: () => ({
            listen: () => {},
            leaveChannel: () => {}
        }),
        private: () => ({
            listen: () => {},
            leaveChannel: () => {}
        }),
        leave: () => {},
        disconnect: () => {}
    };
}
