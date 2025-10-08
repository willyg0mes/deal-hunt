# Estágio 1: BUILD (Compilação e Instalação de Dependências)
FROM php:8.2-fpm-alpine AS builder

# NOVO: Argumentos de Build para injetar variáveis SECRETAS
# O Coolify irá preencher estes ARGs automaticamente se forem definidos
ARG RAWG_API_KEY
ARG APP_KEY

# É uma boa prática definir um ENV a partir do ARG para o processo
ENV RAWG_API_KEY=$RAWG_API_KEY
ENV APP_KEY=$APP_KEY

# 1. Instalação de Ferramentas e Extensões
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

# Instala extensões PHP
RUN docker-php-ext-install pdo pdo_mysql opcache exif pcntl bcmath gd

# Instala o Composer de forma global
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define o diretório de trabalho
WORKDIR /var/www/html

# 2. Cópia Otimizada (CORE APP e DEPENDÊNCIAS)

# CORREÇÃO CRÍTICA: Copie o arquivo ARTISAN e as pastas APP/CONFIG
# Isso garante que 'php artisan' e o autoload de CurrencyHelper.php funcionem.
COPY artisan ./
COPY app ./app
COPY config ./config
COPY routes ./routes
COPY database ./database
COPY public ./public
COPY bootstrap ./bootstrap 

# Copia arquivos de definição de dependência
COPY composer.json composer.lock ./
COPY package.json package-lock.json ./

# 3. Instalação de Dependências
# Instala Composer (PHP) - Agora o 'php artisan package:discover' encontrará o artisan!
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs \
    && chmod -R 777 storage bootstrap/cache
RUN composer install --no-dev --optimize-autoloader

# Instala Node e Compila Assets (Frontend)
RUN npm install && npm run build

# Copia os arquivos remanescentes
COPY . .


# Estágio 2: PRODUCTION (Ambiente de Execução Final)
FROM php:8.2-fpm-alpine AS production

# Instala apenas os pacotes essenciais
RUN apk update && apk add --no-cache \
    nginx \
    mariadb-client \
    bash

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia os arquivos finais do estágio BUILD
COPY --from=builder /var/www/html /var/www/html

# 4. Permissões Críticas
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# 5. Configuração do Servidor
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zz-docker.conf

# 6. Pós-Deploy e Start
# Limpa cache, Roda migrações e Inicia Nginx/PHP-FPM
CMD /bin/bash -c "php artisan config:clear && php artisan migrate --force --no-interaction ; php-fpm -D & nginx -g 'daemon off;'"

# O Nginx está escutando na porta 80
EXPOSE 80
