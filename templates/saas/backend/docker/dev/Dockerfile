FROM ghcr.io/innertia-solutions/php-base-dev:8.4

# Copiar configuraciones de nginx, supervisor y php
COPY ./docker/dev/nginx.conf              /etc/nginx/nginx.conf
COPY ./docker/dev/nginx.default.conf      /etc/nginx/conf.d/default.conf
COPY ./docker/dev/supervisord.conf        /etc/supervisor/conf.d/supervisord.conf
COPY ./docker/dev/php.ini                 /usr/local/etc/php/

WORKDIR /var/www/html

COPY . .

RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80 9000

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
