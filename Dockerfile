FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

RUN chmod -R 775 storage bootstrap/cache

EXPOSE 8000

CMD php artisan config:clear && php artisan migrate --force && php -S 0.0.0.0:8000 -t public/
