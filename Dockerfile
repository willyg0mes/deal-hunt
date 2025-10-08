# Estágio 1: BUILD (Compilação e Instalação de Dependências)
FROM php:8.2-fpm-alpine AS builder

# Argumentos de Build para injetar variáveis SECRETAS
ARG RAWG_API_KEY
ARG APP_KEY

ENV RAWG_API_KEY=$RAWG_API_KEY
ENV APP_KEY=$APP_KEY

# Instala dependências de compilação e runtime
RUN apk update && apk add --no-cache \
    git \
    curl \
    libxml2-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    mariadb-client \
    nodejs \
    npm \
    bash

# Instala extensões PHP (apenas uma vez!)
RUN docker-php-ext-install pdo pdo_mysql opcache exif pcntl bcmath gd

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia arquivos de configuração
COPY composer.json composer.lock ./
COPY package.json package-lock.json ./

# Copia código fonte necessário para o autoload do Composer
COPY artisan ./
COPY app ./app
COPY config ./config
COPY routes ./routes
COPY database ./database
COPY public ./public
COPY bootstrap ./bootstrap
COPY vite.config.js postcss.config.js tailwind.config.js ./
COPY resources ./resources

# Cria diretórios necessários
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs \
    && chmod -R 777 storage

# Agora pode instalar dependências PHP (com todos os arquivos necessários)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Instala dependências Node e compila assets
RUN npm ci --no-audit
RUN npm run build

# Copia arquivos remanescentes
COPY . .

# Permissões finais
RUN chmod -R 775 storage bootstrap/cache


# Estágio 2: PRODUCTION (Ambiente de Execução Final)
FROM php:8.2-fpm-alpine AS production

# Instala apenas dependências de runtime (não precisa compilar extensões novamente!)
RUN apk update && apk add --no-cache \
    nginx \
    bash \
    mariadb-client \
    libpng \
    libjpeg-turbo \
    libxml2

# Copia extensões PHP já compiladas do builder
COPY --from=builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia aplicação completa do builder
COPY --from=builder /var/www/html /var/www/html

# Permissões críticas
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Configuração do Nginx e PHP-FPM
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zz-docker.conf

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD curl -f http://localhost/api/health || exit 1

# Porta exposta
EXPOSE 80

# Comando de inicialização
CMD ["/bin/bash", "-c", "\
    echo '⏳ Aguardando banco de dados...' && sleep 10 && \
    echo '📊 Executando migrações...' && \
    php artisan migrate --force --no-interaction && \
    echo '🧹 Otimizando aplicação...' && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    echo '🚀 Iniciando servidores...' && \
    php-fpm -D && nginx -g 'daemon off;'"]
