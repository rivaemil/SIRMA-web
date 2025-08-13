# -------------------------------
# Etapa 1: dependencias con Composer
# -------------------------------
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-scripts

# -------------------------------
# Etapa 2: PHP-FPM + Nginx
# -------------------------------
FROM php:8.3-fpm

# Paquetes del sistema y headers de libs requeridas por las extensiones
RUN apt-get update && apt-get install -y \
    nginx supervisor git unzip pkg-config \
    libpq-dev libzip-dev libicu-dev libonig-dev \
    && docker-php-ext-install -j$(nproc) pdo pdo_pgsql intl zip opcache mbstring bcmath \
    && rm -rf /var/lib/apt/lists/*

# (Si usas MySQL, añade pdo_mysql)
# RUN docker-php-ext-install pdo_mysql

# Config PHP
COPY docker/php.ini /usr/local/etc/php/conf.d/php.ini

WORKDIR /var/www/html

# Copia primero el resto de la app
COPY . .

# Copia vendor (instalado sin scripts en la etapa 1)
COPY --from=vendor /app/vendor ./vendor

# Permisos
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Nginx + Supervisor
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 10000
CMD ["/usr/bin/supervisord"]
