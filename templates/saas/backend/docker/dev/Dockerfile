FROM php:8.3-apache
RUN apt-get update && apt-get install -y \
    git curl libpq-dev libzip-dev unzip \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
