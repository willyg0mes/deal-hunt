# 🎮 Deal Hunt

**Deal Hunt** é uma plataforma completa de agregação e monitoramento de ofertas de jogos, desenvolvida com Laravel 11. O sistema permite aos usuários descobrirem as melhores promoções, gerenciarem listas de desejos e receberem notificações em tempo real sobre descontos especiais.

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-3.x-cyan.svg)](https://tailwindcss.com)

---

## 📋 Índice

- [Funcionalidades](#-funcionalidades)
- [Tecnologias Utilizadas](#-tecnologias-utilizadas)
- [Requisitos do Sistema](#-requisitos-do-sistema)
- [Instalação](#-instalação)
- [Configuração](#-configuração)
- [Estrutura do Projeto](#-estrutura-do-projeto)
- [Funcionalidades Detalhadas](#-funcionalidades-detalhadas)
- [API e Integrações](#-api-e-integrações)
- [Comandos Artisan](#-comandos-artisan)
- [Testes](#-testes)
- [Deploy](#-deploy)
- [Contribuindo](#-contribuindo)
- [Licença](#-licença)

---

## ✨ Funcionalidades

### 🎯 Principais Recursos

- **🔍 Descoberta de Ofertas**: Navegue por milhares de jogos com descontos atualizados
- **💝 Wishlist Inteligente**: Adicione jogos à sua lista de desejos e receba alertas de promoções
- **🔔 Notificações em Tempo Real**: Sistema de notificações push via Pusher/WebSockets
- **💱 Conversão de Moedas**: Suporte a 7 moedas internacionais (BRL, USD, EUR, GBP, CAD, AUD, JPY)
- **🌐 Tradução Automática**: Descrições de jogos traduzidas automaticamente para PT-BR
- **🔐 Autenticação Segura**: Login com 2FA (Google Authenticator), dispositivos confiáveis
- **📊 Dashboard Administrativo**: Painel completo para gerenciar jogos, traduções e usuários
- **📱 Design Responsivo**: Interface moderna e adaptável para todos os dispositivos
- **🎨 Tema Dark/Light**: Suporte a modo escuro/claro com preferência salva

### 🎮 Recursos Específicos

- Filtros avançados por categoria, preço, desconto e plataforma
- Sistema de busca inteligente com autocomplete
- Histórico de preços e alertas de mínima histórica
- Integração com múltiplas APIs de jogos (RAWG, Steam)
- Cache inteligente para melhor performance
- Sistema de métricas e logs para monitoramento

---

## 🛠️ Tecnologias Utilizadas

### Backend
- **[Laravel 11](https://laravel.com)** - Framework PHP
- **[Laravel Fortify](https://laravel.com/docs/fortify)** - Autenticação
- **[Laravel Sanctum](https://laravel.com/docs/sanctum)** - API authentication
- **[Pusher](https://pusher.com)** - WebSockets e notificações em tempo real

### Frontend
- **[Tailwind CSS 3](https://tailwindcss.com)** - Framework CSS utility-first
- **[Alpine.js](https://alpinejs.dev)** - Framework JavaScript leve
- **[Vite](https://vitejs.dev)** - Build tool moderna e rápida

### Banco de Dados & Cache
- **MySQL/PostgreSQL** - Banco de dados relacional
- **Redis** (opcional) - Cache e filas

### Serviços Externos
- **[RAWG API](https://rawg.io/apidocs)** - Informações sobre jogos
- **[Steam API](https://steamcommunity.com/dev)** - Dados complementares de jogos
- **[ExchangeRate API](https://exchangerate-api.com)** - Conversão de moedas em tempo real
- **Google Translate API** - Tradução automática

---

## 📦 Requisitos do Sistema

- PHP >= 8.2
- Composer 2.x
- Node.js >= 18.x e NPM/Yarn
- MySQL >= 8.0 ou PostgreSQL >= 13
- Redis (opcional, mas recomendado)

### Extensões PHP Necessárias
```
php-cli
php-mbstring
php-xml
php-curl
php-zip
php-mysql (ou php-pgsql)
php-redis (opcional)
```

---

## 🚀 Instalação

### 1. Clone o repositório
```bash
git clone https://github.com/seu-usuario/deal-hunt.git
cd deal-hunt
```

### 2. Instale as dependências PHP
```bash
composer install
```

### 3. Instale as dependências Node.js
```bash
npm install
```

### 4. Configure o arquivo de ambiente
```bash
cp .env.example .env
php artisan key:generate
```

### 5. Configure o banco de dados
Edite o arquivo `.env` com suas credenciais:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=deal_hunt
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

### 6. Execute as migrações
```bash
php artisan migrate --seed
```

### 7. Crie o link simbólico para storage
```bash
php artisan storage:link
```

### 8. Compile os assets
```bash
npm run build
# ou para desenvolvimento
npm run dev
```

### 9. Inicie o servidor
```bash
# Opção 1: Servidor integrado
php artisan serve

# Opção 2: Ambiente completo (recomendado para dev)
composer dev
```

O sistema estará disponível em `http://localhost:8000`

---

## ⚙️ Configuração

### Configuração de APIs

#### RAWG API
1. Crie uma conta em [RAWG.io](https://rawg.io/apidocs)
2. Obtenha sua API key
3. Configure no `.env`:
```env
RAWG_API_KEY=sua_chave_api_rawg
```

#### Pusher (Notificações em Tempo Real)
1. Crie uma conta em [Pusher.com](https://pusher.com)
2. Crie uma nova aplicação
3. Configure no `.env`:
```env
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=seu_app_id
PUSHER_APP_KEY=sua_app_key
PUSHER_APP_SECRET=seu_app_secret
PUSHER_APP_CLUSTER=seu_cluster
```

#### Google Translate (Tradução)
Configure a chave da API do Google Cloud Translation:
```env
GOOGLE_TRANSLATE_API_KEY=sua_chave_google_translate
```

### Configuração de Filas

Para processar jobs em background (traduções, notificações, etc):

```bash
# Configure no .env
QUEUE_CONNECTION=database
# ou
QUEUE_CONNECTION=redis

# Inicie o worker
php artisan queue:work --tries=3
```

### Configuração de Cache

Recomendado usar Redis para melhor performance:
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

---

## 📁 Estrutura do Projeto

```
deal-hunt/
├── app/
│   ├── Actions/          # Ações customizadas (Fortify, etc)
│   ├── Console/          # Comandos Artisan
│   ├── Events/           # Eventos do sistema
│   ├── Helpers/          # Funções auxiliares (CurrencyHelper)
│   ├── Http/
│   │   ├── Controllers/  # Controllers MVC
│   │   └── Middleware/   # Auth, admin, trusted device
│   ├── Jobs/             # Jobs de fila (traduções, scraping)
│   ├── Models/           # Modelos Eloquent
│   ├── Notifications/    # Notificações (email, push)
│   ├── Observers/        # Observers do Eloquent
│   ├── Providers/        # Service Providers
│   └── Services/         # Serviços (RAWG, Translation, Currency)
├── database/
│   ├── migrations/       # Migrações do banco de dados
│   ├── seeders/          # Seeders
│   └── factories/        # Factories para testes
├── resources/
│   ├── views/            # Blade templates
│   ├── js/               # JavaScript/Alpine
│   └── css/              # Tailwind CSS
├── routes/
│   ├── web.php           # Rotas web
│   ├── api.php           # Rotas API
│   ├── channels.php      # Broadcasting channels
│   └── console.php       # Comandos de console
├── public/               # Assets públicos
├── storage/              # Arquivos, logs, cache
└── tests/                # Testes automatizados
```

---

## 🎯 Funcionalidades Detalhadas

### Sistema de Wishlist

Os usuários podem adicionar jogos à sua wishlist e receber notificações quando:
- O jogo entrar em promoção
- O desconto ultrapassar um limite definido
- O preço atingir uma mínima histórica

**Exemplo de uso:**
```php
// Controller
$user->wishlist()->attach($gameId);

// Verificar se está na wishlist
$isInWishlist = $user->wishlist()->where('game_id', $gameId)->exists();
```

### Sistema de Notificações

Notificações em tempo real usando Pusher WebSockets:

```javascript
// Frontend - Listen for notifications
Echo.private(`user.${userId}`)
    .notification((notification) => {
        // Exibir notificação
        showToast(notification.message);
    });
```

### Conversão de Moedas

Sistema automático que converte preços para a moeda preferida do usuário:

```php
use App\Services\CurrencyConversionService;

$converter = new CurrencyConversionService();

// Converter USD para BRL
$priceInBRL = $converter->convertFromUSD(59.99, 'BRL');

// Formatar valor
$formatted = $converter->formatCurrency($priceInBRL, 'BRL', 'pt-BR');
// Resultado: R$ 311,95
```

### Tradução Automática

Sistema de tradução com múltiplas estratégias de fallback:

```php
use App\Services\TranslationService;

$translator = new TranslationService();

$result = $translator->translateText($englishText, 'pt');
// Retorna: ['success' => true, 'translation' => '...', 'method' => 'google_api']
```

### Autenticação 2FA

Implementação de autenticação de dois fatores usando Google Authenticator:

1. Usuário ativa 2FA no perfil
2. QR Code é gerado
3. A cada login, código de 6 dígitos é solicitado
4. Dispositivos confiáveis podem pular 2FA por 30 dias

---

## 🔌 API e Integrações

### RAWG Service

Serviço para buscar informações de jogos:

```php
use App\Services\RawgService;

$rawg = new RawgService();

// Buscar jogo
$game = $rawg->searchGame('Cyberpunk 2077');

// Obter detalhes completos
$details = $rawg->getGameDetails('The Witcher 3');

// Buscar melhor imagem
$imageUrl = $rawg->getBestGameImage('Red Dead Redemption 2');
```

### Endpoints API

```
GET  /api/games              # Lista jogos
GET  /api/games/{id}         # Detalhes do jogo
POST /api/wishlist/add       # Adicionar à wishlist
POST /api/wishlist/remove    # Remover da wishlist
GET  /api/notifications      # Listar notificações
```

---

## 🎮 Comandos Artisan

### Comandos Personalizados

```bash
# Sincronizar jogos com APIs externas
php artisan games:sync

# Processar traduções pendentes
php artisan translations:process

# Atualizar taxas de câmbio
php artisan currency:update

# Enviar notificações de promoções
php artisan notifications:send-deals

# Limpar cache do sistema
php artisan cache:clear-all

# Enviar notificação de teste
php artisan notifications:test
```

### Comandos de Desenvolvimento

```bash
# Rodar ambiente completo (server + queue + logs + vite)
composer dev

# Rodar testes
composer test
# ou
php artisan test
```

---

## 🧪 Testes

O projeto utiliza **Pest PHP** para testes:

```bash
# Rodar todos os testes
php artisan test

# Rodar testes com cobertura
php artisan test --coverage

# Rodar testes específicos
php artisan test --filter=UserTest

# Rodar testes em paralelo
php artisan test --parallel
```

### Estrutura de Testes

```
tests/
├── Feature/          # Testes de integração
│   ├── Auth/
│   ├── Games/
│   └── Wishlist/
└── Unit/             # Testes unitários
    ├── Services/
    └── Helpers/
```

---

## 🚢 Deploy

### Opção 1: Docker Compose (Recomendado)

O projeto inclui configuração completa de Docker Compose com todos os serviços necessários:

```bash
# Clone o repositório
git clone https://github.com/seu-usuario/deal-hunt.git
cd deal-hunt

# Configure as variáveis de ambiente
cp ENV_DOCKER_EXAMPLE.md .env
# Edite o .env com suas configurações

# Inicie todos os serviços
docker-compose up -d

# Verifique os logs
docker-compose logs -f
```

**Serviços incluídos:**
- ✅ App Laravel (Nginx + PHP-FPM)
- ✅ MySQL 8.0
- ✅ Redis 7
- ✅ Queue Worker
- ✅ Task Scheduler
- ✅ phpMyAdmin (desenvolvimento)

### Opção 2: Deploy no Coolify

O projeto está otimizado para deploy no **Coolify** (alternativa self-hosted ao Heroku/Vercel).

📖 **[Guia Completo de Deploy no Coolify](DEPLOY_COOLIFY.md)**

**Resumo rápido:**
1. Crie um projeto no Coolify
2. Conecte o repositório Git
3. Configure variáveis de ambiente (veja `ENV_DOCKER_EXAMPLE.md`)
4. Configure domínio e SSL
5. Deploy!

### Opção 3: Docker Standalone

Para executar apenas a aplicação:

```bash
# Build da imagem
docker build -t deal-hunt .

# Executar container
docker run -d -p 8000:8000 \
  -e APP_KEY=your_app_key \
  -e DB_HOST=seu_db_host \
  -e DB_PASSWORD=sua_senha \
  deal-hunt
```

### Deploy em Produção (VPS Tradicional)

1. **Configure variáveis de ambiente**:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seu-dominio.com
```

2. **Otimize o sistema**:
```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

3. **Configure o servidor web** (Nginx/Apache)

Exemplo de configuração Nginx:
```nginx
server {
    listen 80;
    server_name seu-dominio.com;
    root /var/www/deal-hunt/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

4. **Configure supervisor para queues**:
```ini
[program:deal-hunt-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/deal-hunt/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/deal-hunt/storage/logs/worker.log
```

---

## 🎨 Customização

### Temas e Cores

O projeto usa Tailwind CSS. Personalize cores em `tailwind.config.js`:

```javascript
module.exports = {
  theme: {
    extend: {
      colors: {
        primary: '#ff0000',
        secondary: '#00ff00',
      }
    }
  }
}
```

### Adicionar Nova Moeda

1. Edite `app/Services/CurrencyConversionService.php`
2. Adicione a moeda em `SUPPORTED_CURRENCIES`
3. Atualize o formulário de seleção de moeda

---

## 🤝 Contribuindo

Contribuições são bem-vindas! Para contribuir:

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/MinhaFeature`)
3. Commit suas mudanças (`git commit -m 'Adiciona MinhaFeature'`)
4. Push para a branch (`git push origin feature/MinhaFeature`)
5. Abra um Pull Request

### Diretrizes de Contribuição

- Siga o PSR-12 para código PHP
- Escreva testes para novas funcionalidades
- Documente funções e classes complexas
- Mantenha commits atômicos e bem descritos

---

## 📄 Licença

Este projeto está licenciado sob a [MIT License](LICENSE).

---

## 👨‍💻 Autor

**Willy G**

---

## 🙏 Agradecimentos

- [Laravel](https://laravel.com) - Framework excepcional
- [Fortify](https://laravel.com/docs/fortify) - Autenticação
- [RAWG](https://rawg.io) - API de jogos
- [Tailwind CSS](https://tailwindcss.com) - Framework CSS incrível

---

<div align="center">
  <strong>🎮 Happy Gaming & Happy Hunting! 🎯</strong>
</div>

