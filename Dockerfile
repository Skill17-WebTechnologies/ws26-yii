FROM php:8.3-cli-bookworm
COPY --from=composer:2.9.5 /usr/bin/composer /usr/bin/composer
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libicu-dev libonig-dev libxml2-dev \
        libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install -j"$(nproc)" intl pdo_mysql mysqli zip bcmath gd exif pcntl sockets mbstring dom xml \
    && rm -rf /var/lib/apt/lists/*
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-scripts --no-security-blocking
COPY . .
COPY docker-entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint && chmod -R 777 runtime web/assets
EXPOSE 8080
ENTRYPOINT ["entrypoint"]
