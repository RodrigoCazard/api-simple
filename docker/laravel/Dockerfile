FROM composer:2 AS composer_binary

# Laravel 13 requiere PHP 8.3 o superior.
FROM php:8.3-apache

ARG DEBIAN_FRONTEND=noninteractive

# pdo_mysql conecta Eloquent con MySQL; mod_rewrite envía todas las rutas a
# public/index.php, el único punto de entrada web seguro de Laravel.
RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_mysql \
    && a2enmod rewrite

COPY --from=composer_binary /usr/bin/composer /usr/local/bin/composer
COPY docker/laravel/apache.conf /etc/apache2/sites-available/000-default.conf
COPY nivel-3/ /var/www/html/

# vendor se genera dentro de Linux y storage queda escribible para Laravel.
RUN composer install \
        --working-dir=/var/www/html \
        --no-dev \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

# Las migraciones se aplican al iniciar. Los usuarios y productos de prueba se
# cargan únicamente fuera de production para no crear claves demo en producción.
CMD ["sh", "-c", "php artisan migrate --force && if [ \"$APP_ENV\" != \"production\" ]; then php artisan db:seed --force; fi && apache2-foreground"]
