FROM php:8.3-cli

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    unzip \
    && docker-php-ext-install \
    bcmath \
    intl \
    mbstring \
    pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install \
    --prefer-dist \
    --no-interaction \
    --optimize-autoloader \
    --ignore-platform-reqs \
    --no-scripts

COPY docker/start.sh /usr/local/bin/start-app

RUN chmod +x /usr/local/bin/start-app \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000

CMD ["start-app"]
