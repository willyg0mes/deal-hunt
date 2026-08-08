# Variáveis de Ambiente para Docker/Coolify

Copie este arquivo para `.env` no Coolify ou configure as variáveis diretamente na interface do Coolify.

```env
# ================================
# APP CONFIGURATION
# ================================
APP_NAME="Deal Hunt"
APP_ENV=production
APP_KEY=base64:GERAR_COM_php_artisan_key:generate
APP_DEBUG=false
APP_URL=https://seu-dominio.com
APP_PORT=8000

# ================================
# DATABASE CONFIGURATION
# ================================
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=deal_hunt
DB_USERNAME=deal_hunt_user
DB_PASSWORD=SENHA_SUPER_SEGURA_AQUI
DB_ROOT_PASSWORD=ROOT_PASSWORD_SUPER_SEGURA

# Porta externa para acesso ao MySQL (opcional)
DB_PORT_EXTERNAL=3307

# ================================
# REDIS CONFIGURATION
# ================================
REDIS_HOST=redis
REDIS_PASSWORD=REDIS_PASSWORD_SEGURA
REDIS_PORT=6379
REDIS_PORT_EXTERNAL=6380

# ================================
# CACHE & SESSIONS
# ================================
CACHE_STORE=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120
QUEUE_CONNECTION=redis

# ================================
# BROADCASTING (Pusher)
# ================================
# Obtenha em: https://pusher.com
BROADCAST_DRIVER=pusher
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=seu_app_id
PUSHER_APP_KEY=sua_app_key
PUSHER_APP_SECRET=seu_app_secret
PUSHER_APP_CLUSTER=mt1

# ================================
# EXTERNAL APIs
# ================================
# RAWG API - https://rawg.io/apidocs
RAWG_API_KEY=sua_rawg_api_key

# ================================
# MAIL CONFIGURATION
# ================================
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=seu_username
MAIL_PASSWORD=sua_senha
MAIL_SCHEME=null
MAIL_FROM_ADDRESS=noreply@dealhunt.com
MAIL_FROM_NAME="${APP_NAME}"

# Locales / moeda
APP_LOCALE=pt-BR
APP_FALLBACK_LOCALE=pt-BR
APP_DEFAULT_CURRENCY=BRL
APP_DEFAULT_LOCALE=pt-BR

# ================================
# PHPMYADMIN (Desenvolvimento)
# ================================
PMA_PORT=8080

# ================================
# LOGGING
# ================================
LOG_CHANNEL=stack
LOG_LEVEL=warning
LOG_DEPRECATIONS_CHANNEL=null

# ================================
# TRUSTED PROXIES (Importante para Coolify)
# ================================
# Coolify usa proxy reverso, então precisamos confiar nele
TRUSTED_PROXIES=*
```

## Variáveis Obrigatórias

As seguintes variáveis **DEVEM** ser configuradas:

1. `APP_KEY` - Gere com: `php artisan key:generate --show`
2. `DB_PASSWORD` - Senha do banco de dados
3. `DB_ROOT_PASSWORD` - Senha root do MySQL
4. `REDIS_PASSWORD` - Senha do Redis
5. `RAWG_API_KEY` - Chave da API RAWG

## Variáveis Opcionais mas Recomendadas

1. `PUSHER_APP_ID`, `PUSHER_APP_KEY`, `PUSHER_APP_SECRET` - Para notificações em tempo real
2. `GOOGLE_TRANSLATE_API_KEY` - Para tradução automática
3. `MAIL_*` - Configurações de email

## Como Gerar APP_KEY

```bash
# Localmente
php artisan key:generate --show

# Ou use este comando online
docker run --rm php:8.2-cli php -r "echo 'base64:' . base64_encode(random_bytes(32)) . PHP_EOL;"
```

