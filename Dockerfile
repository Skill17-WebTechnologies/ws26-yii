FROM php:8.3-cli-bookworm
COPY --from=composer:2.9.5 /usr/bin/composer /usr/bin/composer
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libicu-dev libonig-dev libxml2-dev libsqlite3-dev \
        libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    # pdo_mysql is what config/db.php uses: the app talks to the competitor's own
    # MySQL database, configured entirely through .env / .env.prod. Without it the
    # app fails with "could not find driver" the moment it is pointed at MySQL.
    # mysqlnd ships with the PHP image, so it needs no extra system library.
    # (`sqlite3` is deliberately absent: it is already compiled in, and naming it
    # here fails the build with "Cannot find config.m4".)
    && docker-php-ext-install -j"$(nproc)" intl pdo_mysql pdo_sqlite zip bcmath gd exif pcntl sockets mbstring dom xml \
    && rm -rf /var/lib/apt/lists/*
WORKDIR /app
COPY composer.json composer.lock ./
ARG COMPOSER_REGISTRY=https://repo.packagist.org
RUN composer config -g repos.packagist composer "$COMPOSER_REGISTRY"
RUN composer install --no-interaction --prefer-dist --no-scripts --no-security-blocking
COPY . .
COPY docker-entrypoint.sh /usr/local/bin/entrypoint
# Strip any CR before making the entrypoint executable. .gitattributes already
# forces LF on checkout, but that only helps a fresh clone — this keeps a working
# copy that was checked out before it, or copied off a Windows share, from
# producing "env: 'bash\r': No such file or directory" and exit 127.
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint \
    && chmod +x /usr/local/bin/entrypoint && chmod -R 777 runtime web/assets
EXPOSE 80
ENTRYPOINT ["entrypoint"]
