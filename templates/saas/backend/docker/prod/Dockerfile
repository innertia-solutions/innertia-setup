# ── Builder: instala dependencias de Composer optimizadas ─────────────────────
FROM ghcr.io/innertia-solutions/php-base-prod:8.4 AS builder

COPY ./docker/prod/php.ini /usr/local/etc/php/conf.d/custom-php.ini

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

RUN composer dump-autoload --optimize --classmap-authoritative --no-dev --no-interaction

# ── Final: imagen limpia con el código ya listo ────────────────────────────────
FROM ghcr.io/innertia-solutions/php-base-prod:8.4

COPY ./docker/prod/php.ini /usr/local/etc/php/conf.d/custom-php.ini

COPY --from=builder /var/www/html /var/www/html

RUN chown -R www-data:www-data /var/www/html \
    && mkdir -p \
        storage/logs \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

EXPOSE 80

CMD ["sh", "-lc", "php artisan octane:start --server=swoole --host=0.0.0.0 --port=${APP_PORT:-80}"]
