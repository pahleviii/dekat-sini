FROM php:8.4-cli AS build
RUN apt-get update && apt-get install -y git unzip libicu-dev libzip-dev libpng-dev libjpeg-dev libwebp-dev nodejs npm \
 && docker-php-ext-configure gd --with-jpeg --with-webp \
 && docker-php-ext-install pdo_mysql intl zip gd opcache
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader \
 && npm install && npm run build \
 && php artisan storage:link || true

FROM php:8.4-apache
RUN apt-get update && apt-get install -y libicu-dev libzip-dev libpng-dev libjpeg-dev libwebp-dev \
 && docker-php-ext-configure gd --with-jpeg --with-webp \
 && docker-php-ext-install pdo_mysql intl zip gd opcache \
 && a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
COPY --from=build /app /var/www/html
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
CMD php artisan migrate --force && apache2-foreground
