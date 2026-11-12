FROM dunglas/frankenphp:1-php8.5-alpine

RUN apk add --no-cache git unzip \
    && install-php-extensions pdo_pgsql intl opcache zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    SERVER_NAME=:80

WORKDIR /app
