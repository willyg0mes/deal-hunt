# Deal Hunt

Agregador de ofertas de jogos digitais: coleta promoções (CheapShark), enriquece metadados (RAWG/Steam), wishlist com alertas e painel admin.

**Stack:** Laravel 11 · PHP 8.2+ · Tailwind CSS · Alpine.js · Vite · MySQL/SQLite · Redis (opcional) · Pusher

Repositório: [github.com/willyg0mes/deal-hunt](https://github.com/willyg0mes/deal-hunt)

---

## O que faz

- Catálogo de jogos em promoção, com filtros por preço, desconto e loja
- Wishlist com notificações quando o preço cai
- Conversão de moedas (BRL, USD, EUR, etc.)
- Tradução de descrições para PT-BR
- Auth com 2FA (Google Authenticator) e dispositivos confiáveis
- Dashboard admin (jogos, categorias, usuários, métricas)

Fontes principais: **CheapShark** (deals), **RAWG** (detalhes/imagens), **Steam** (complemento).

---

## Requisitos

- PHP 8.2+
- Composer 2
- Node.js 18+
- MySQL 8 / PostgreSQL 13 **ou** SQLite (dev)
- Redis (recomendado em produção, para cache/filas)

---

## Setup rápido (local)

```bash
git clone https://github.com/willyg0mes/deal-hunt.git
cd deal-hunt

composer install
npm install

cp .env.example .env
php artisan key:generate

# SQLite (padrão do .env.example) ou configure MySQL no .env
touch database/database.sqlite   # se usar sqlite
php artisan migrate --seed

php artisan storage:link
npm run build

php artisan serve
# http://localhost:8000
```

Para dev com queue + Vite juntos:

```bash
composer dev
```

---

## Variáveis de ambiente

Copie `.env.example` → `.env`. Chaves importantes (todas **placeholders** no exemplo — nunca commite `.env`):

| Variável | Uso |
|----------|-----|
| `RAWG_API_KEY` | Metadados/imagens de jogos |
| `PUSHER_APP_*` | Notificações em tempo real |
| `DB_*` | Banco |
| `QUEUE_CONNECTION` | `database` ou `redis` |
| `CACHE_STORE` / `SESSION_DRIVER` | Preferível `redis` em produção |

Docker/Coolify: veja `ENV_DOCKER_EXAMPLE.md` e `DEPLOY_COOLIFY.md`.

---

## Comandos úteis

```bash
# Coleta de ofertas
php artisan collect:deals
php artisan games:fetch-deals
php artisan games:fetch-free

# Manutenção
php artisan deals:clean-expired
php artisan games:update-images
php artisan games:translate
php artisan currency:update-rates

# Notificações
php artisan notification:game-promotions
```

Worker de filas:

```bash
php artisan queue:work --tries=3
```

---

## Docker

```bash
cp ENV_DOCKER_EXAMPLE.md .env   # edite senhas e chaves
# ou: cp .env.example .env && ajuste para os serviços do compose

docker compose up -d
docker compose logs -f
```

Serviços típicos: app (Nginx + PHP-FPM), MySQL, Redis, queue worker, scheduler.

---

## Testes

```bash
php artisan test
# ou
composer test
```

---

## Estrutura (resumo)

```
app/
  Console/Commands/   # coleta de deals, traduções, notificações
  Services/           # RAWG, Steam, câmbio, tradução
  Http/Controllers/   # web + admin
resources/views/      # Blade + Tailwind
routes/               # web, api, channels, console
docker/               # configs de container
```

---

## Segurança / repo público

- `.env` está no `.gitignore` — use só `.env.example` / `ENV_DOCKER_EXAMPLE.md`
- Não versionar keystores, dumps de banco ou chaves reais de API
- Em produção: `APP_DEBUG=false`, HTTPS, filas com supervisor/systemd

---

## Licença

MIT — veja [LICENSE](LICENSE) se presente no repositório.

---

Feito por **Willy G** · [github.com/willyg0mes](https://github.com/willyg0mes)
