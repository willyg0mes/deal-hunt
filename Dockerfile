# -- FASE 1: BUILD (Instala dependências do Composer e Node) --
# Use a imagem oficial do PHP que inclui FPM e as extensões necessárias
FROM php:8.2-fpm-alpine AS builder

# Instale dependências do sistema necessárias para PHP, Node e banco de dados
RUN apk update && apk add --no-cache \
    nginx \
    git \
    curl \
    libxml2-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    mariadb-client \
    nodejs \
    npm

# Instale extensões PHP (ajuste conforme seu 'composer.json')
RUN docker-php-ext-install pdo pdo_mysql opcache exif pcntl bcmath gd

# Instale o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Defina o diretório de trabalho
WORKDIR /var/www/html

# Copie apenas os arquivos de dependência primeiro para aproveitar o cache do Docker
COPY composer.json composer.lock ./
COPY package.json package-lock.json ./

# Instale as dependências do Composer (PHP)
RUN composer install --no-dev --optimize-autoloader

# Instale as dependências do Node e execute o build do frontend (React/Blade)
RUN npm install && npm run build

# Copie o restante do código da aplicação
COPY . .

# -- FASE 2: PRODUÇÃO (Ambiente de execução final) --
# Use uma imagem menor para o ambiente final de produção
FROM php:8.2-fpm-alpine AS production

# Instale apenas os pacotes essenciais e o Nginx
RUN apk update && apk add --no-cache \
    nginx \
    mariadb-client \
    bash

# Defina o diretório de trabalho
WORKDIR /var/www/html

# Copie os arquivos de build da Fase 1 (Código, assets, cache, vendors)
COPY --from=builder /var/www/html /var/www/html

# Ajuste as permissões de cache e logs para o usuário do Nginx/PHP-FPM
# Este passo é CRÍTICO para resolver o status "Degradado" por permissão
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copie arquivos de configuração customizados (Nginx e PHP-FPM)
# Estes arquivos devem estar no mesmo diretório do Dockerfile
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zz-docker.conf

# Exponha a porta do Nginx
EXPOSE 80

# Comando para iniciar o PHP-FPM e o Nginx
CMD php-fpm -D && nginx -g "daemon off;"
