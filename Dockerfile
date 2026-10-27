FROM php:8.4-cli

# Install system dependencies & PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libssl-dev \
    libcurl4-openssl-dev \
    libzip-dev \
    pkg-config \
    && docker-php-ext-install zip \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader

# Storage aur cache permissions set karein
RUN chmod -R 777 storage bootstrap/cache

EXPOSE 8080
CMD php artisan config:clear && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}