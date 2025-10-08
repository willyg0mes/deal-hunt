# Estágio 1: BUILD (Compilação e Instalação de Dependências)
# Usa uma imagem base do PHP com FPM e Alpine (mais leve)
FROM php:8.2-fpm-alpine AS builder

# 1. Instalação de Ferramentas e Extensões
# Instala pacotes do sistema necessários para PHP, Node, Git e DB
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

# Instala extensões PHP (ajuste conforme o seu projeto)
RUN docker-php-ext-install pdo pdo_mysql opcache exif pcntl bcmath gd

# Instala o Composer de forma global
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define o diretório de trabalho
WORKDIR /var/www/html

# 2. Cópia Otimizada (CORE APP e DEPENDÊNCIAS)
# COPIAR ARQUIVOS DE AUTOLOAD (Correção para o erro CurrencyHelper.php)
# O Composer precisa desses arquivos antes de rodar a instalação.
COPY app ./app
COPY config ./config
COPY routes ./routes
COPY database ./database
COPY public ./public
# Copie o que mais for referenciado no autoloader (Providers, etc.)

# Copia arquivos de definição de dependência
COPY composer.json composer.lock ./
COPY package.json package-lock.json ./

# 3. Instalação de Dependências
# Instala Composer (PHP)
RUN composer install --no-dev --optimize-autoloader

# Instala Node e Compila Assets (Frontend)
RUN npm install && npm run build

# Copia os arquivos remanescentes que não afetam o cache de dependências
COPY . .


# Estágio 2: PRODUCTION (Ambiente de Execução Final)
# Usa uma imagem PHP FPM limpa e menor para o ambiente final
FROM php:8.2-fpm-alpine AS production

# Instala apenas os pacotes essenciais para rodar (Nginx e Cliente MariaDB)
RUN apk update && apk add --no-cache \
    nginx \
    mariadb-client \
    bash

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia os arquivos finais do estágio BUILD (incluindo vendors e assets compilados)
COPY --from=builder /var/www/html /var/www/html

# 4. Permissões Críticas
# Garante que o PHP-FPM (usuário www-data) possa escrever em storage e cache
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# 5. Configuração do Servidor
# Copia as configurações do Nginx e PHP-FPM (Assumindo que estão na pasta 'docker')
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zz-docker.conf

# 6. Pós-Deploy (Opcional, mas Recomendado)
# Este script pode ser usado para rodar comandos antes do servidor iniciar
# Adicionamos a migração aqui, garantindo que o DB esteja configurado
# Se a migração falhar, o container cairá, facilitando o diagnóstico.
# Este é o comando de start do container:
CMD /bin/bash -c "php artisan config:clear && php artisan migrate --force --no-interaction ; php-fpm -D & nginx -g 'daemon off;'"

# O Nginx está escutando na porta 80
EXPOSE 80
