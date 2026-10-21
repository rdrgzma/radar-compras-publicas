# ==============================================================================
# Dockerfile para radar-painel (PHP 8.4 + Laravel 13 + FilamentPHP 5)
# ==============================================================================

FROM php:8.4-cli-alpine

WORKDIR /var/www/html

# Instalar extensões essenciais para Laravel e PostgreSQL
RUN apk add --no-cache \
    curl \
    git \
    unzip \
    libzip-dev \
    postgresql-dev \
    icu-dev \
    tzdata \
    && docker-php-ext-install -j$(nproc) pdo pdo_pgsql pgsql intl zip pcntl opcache \
    && cp /usr/share/zoneinfo/America/Sao_Paulo /etc/localtime \
    && echo "America/Sao_Paulo" > /etc/timezone

# Instalar Composer oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar arquivos de configuração de dependências
COPY composer.json composer.lock* ./

# Instalar dependências PHP
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev || true

# Copiar o restante da aplicação
COPY . .

# Permissões do storage e bootstrap/cache
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8080

CMD ["sh", "-c", "php artisan migrate --force && php artisan filament:assets && php artisan serve --host=0.0.0.0 --port=8080"]
