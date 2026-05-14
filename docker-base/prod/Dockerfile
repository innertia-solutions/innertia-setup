FROM phpswoole/swoole:6.1.0-php8.4-alpine

# Instalar extensiones PHP necesarias para producción.
# Swoole ya viene incluido en la imagen base.
# Esta imagen se pre-construye y se publica en ghcr.io.
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
    && rm -rf /var/cache/apk/* \
    && curl -fsSL https://getcomposer.org/installer \
       | php -- --install-dir=/usr/bin --filename=composer

WORKDIR /var/www/html
