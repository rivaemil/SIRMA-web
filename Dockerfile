FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libzip-dev unzip git curl \
    && docker-php-ext-install zip pdo pdo_mysql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . /var/www/html
WORKDIR /var/www/html

RUN echo "DocumentRoot /var/www/html/public" >> /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite
