FROM php:8.4-apache

RUN apt-get update && apt-get install -y \
    git unzip zip libpq-dev libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql zip

RUN php -m | grep pgsql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data storage bootstrap/cache

RUN php artisan config:clear
RUN php artisan route:clear
RUN php artisan view:clear

RUN a2enmod rewrite

COPY ./apache.conf /etc/apache2/sites-available/000-default.conf

CMD php artisan migrate --force && apache2-foreground

COPY docker/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini