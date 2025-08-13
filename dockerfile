# ---------------------------------------
# Etapa 1: dependencias PHP con Composer
# ---------------------------------------
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# ---------------------------------------
# Etapa 2: App + PHP-FPM + Nginx
# ---------------------------------------
FROM php:8.3-fpm

# Extensiones necesarias (pgsql y/o mysql)
RUN apt-get update && apt-get install -y \
    nginx supervisor git unzip libpq-dev libonig-dev libzip-dev libicu-dev \
    && docker-php-ext-install pdo pdo_pgsql intl zip opcache \
    && rm -rf /var/lib/apt/lists/*

# Si usarás MySQL en lugar de Postgres, descomenta:
# RUN docker-php-ext-install pdo_mysql

# Config PHP (opcache recomendado)
COPY docker/php.ini /usr/local/etc/php/conf.d/php.ini

# Copia aplicación
WORKDIR /var/www/html
COPY . .
# Copia vendor de la etapa 1
COPY --from=vendor /app/vendor ./vendor

# Permisos storage/bootstrap
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Nginx confi y supervisor
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Expón el puerto web
EXPOSE 10000

# Comandos de optimización (se ejecutarán en postDeploy también)
# No hacemos key:generate aquí porque dependerá de APP_KEY de entorno

# Entrypoint: Supervisor levanta php-fpm y nginx
CMD ["/usr/bin/supervisord"]
