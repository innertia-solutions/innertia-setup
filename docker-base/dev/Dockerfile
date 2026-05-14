FROM php:8.4-fpm-alpine

# Instalar dependencias del sistema + extensiones PHP + Xdebug + Redis
# Esta imagen se pre-construye una sola vez y se publica en ghcr.io.
# Los proyectos hacen FROM de esta imagen para evitar recompilar en cada build.
RUN apk add --no-cache \
        git unzip curl \
        linux-headers \
        nginx supervisor \
        postgresql-dev libzip-dev zlib-dev \
        libpng-dev libjpeg-turbo-dev freetype-dev libwebp-dev \
        libxml2-dev icu-dev oniguruma-dev \
        $PHPIZE_DEPS \
    && pecl install xdebug redis \
    && docker-php-ext-enable xdebug redis \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql pgsql mysqli pdo_mysql pcntl \
        gd zip dom simplexml xml xmlreader xmlwriter mbstring intl \
    && apk del $PHPIZE_DEPS linux-headers \
    && rm -rf /tmp/pear /var/cache/apk/* \
    && curl -sS https://getcomposer.org/installer \
       | php -- --install-dir=/usr/bin --filename=composer

# Xdebug: modo debug, conexión a host (IDE en el host)
RUN XDEBUG_SO=$(find /usr/local/lib/php/extensions/ -name xdebug.so) \
    && echo "zend_extension=${XDEBUG_SO}" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.mode=debug"                          >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.start_with_request=yes"              >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal"    >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.client_port=9006"                    >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.log=/tmp/xdebug.log"                 >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.log_level=0"                         >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.idekey=vscode"                       >> /usr/local/etc/php/conf.d/xdebug.ini

WORKDIR /var/www/html
