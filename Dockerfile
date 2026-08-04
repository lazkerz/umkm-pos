FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libonig-dev libxml2-dev curl \
    && docker-php-ext-install pdo_mysql zip gd \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction
RUN npm install && npm run build && rm -rf node_modules

RUN chmod -R 775 storage bootstrap/cache

EXPOSE 8080
CMD php artisan migrate --force && php artisan storage:link || true && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
