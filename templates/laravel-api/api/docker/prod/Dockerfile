# Etapa base: runtime + extensiones (Swoole ya viene preinstalado)
FROM phpswoole/swoole:6.1.0-php8.4-alpine AS php-base

ENV APP_ENV=production
ENV APP_DEBUG=false

RUN apk add --no-cache \
        git unzip curl \
        libstdc++ libpq libzip \
        libpng libjpeg-turbo freetype libwebp \
        icu-libs \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS postgresql-dev \
        libzip-dev zlib-dev \
        libpng-dev libjpeg-turbo-dev freetype-dev libwebp-dev \
        icu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql pgsql pcntl \
        gd zip intl \
    && apk del .build-deps \
    && curl -fsSL https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

COPY ./docker/prod/php.ini /usr/local/etc/php/conf.d/custom-php.ini

WORKDIR /var/www/html

# Etapa builder: instala vendor optimizado
FROM php-base AS builder

COPY composer.json composer.lock ./
RUN composer install \
      --no-dev \
      --prefer-dist \
      --optimize-autoloader \
      --no-scripts \
      --no-progress \
      --no-interaction \
    && composer clear-cache

COPY . .

# Recalcular autoload con el código de la app ya copiado
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev --no-interaction

# Etapa final
FROM php-base

COPY --from=builder /var/www/html /var/www/html

RUN chown -R www-data:www-data /var/www/html \
    && mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/www/html/storage/framework/cache/data \
    && mkdir -p /var/www/html/storage/framework/sessions \
    && mkdir -p /var/www/html/storage/framework/views \
    && mkdir -p /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R ug+rwx /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD ["sh", "-lc", "php artisan octane:start --server=swoole --host=0.0.0.0 --port=${APP_PORT:-80}"]
