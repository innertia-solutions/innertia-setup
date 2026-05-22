# Usar la imagen base de PHP con FPM y Alpine
FROM php:8.4-fpm-alpine

# Instalar dependencias del sistema y Composer
RUN apk add --no-cache \
    git \
    unzip \
    curl \
    linux-headers \
    nginx \
    supervisor \
    postgresql-dev \
    libzip-dev \
    zlib-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libwebp-dev \
    libxml2-dev \
    icu-dev \
    oniguruma-dev \
    $PHPIZE_DEPS \
    && pecl install xdebug redis \
    && docker-php-ext-enable xdebug redis \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql pgsql mysqli pdo_mysql pcntl \
        gd zip dom simplexml xml xmlreader xmlwriter mbstring intl \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# Configurar Xdebug
RUN echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.client_port=9006" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.log=/tmp/xdebug.log" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.log_level=0" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.idekey=vscode" >> /usr/local/etc/php/conf.d/xdebug.ini

# Copiar configuraciones
COPY ./docker/dev/nginx.conf /etc/nginx/nginx.conf
COPY ./docker/dev/nginx.default.conf /etc/nginx/conf.d/default.conf
COPY ./docker/dev/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY ./docker/dev/php.ini /usr/local/etc/php/

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos de la aplicación
COPY . .

# Crear los directorios necesarios y establecer permisos
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Exponer los puertos
EXPOSE 80 9000

# Comando para iniciar Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
