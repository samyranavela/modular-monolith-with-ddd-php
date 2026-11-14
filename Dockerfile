FROM dunglas/frankenphp:1-php8.5-alpine

RUN apk add --no-cache git unzip \
    && install-php-extensions pdo_pgsql intl opcache zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# The container runs as the host user (see compose.yaml): FrankenPHP must bind port 80 without being root, and
# Caddy, Composer and Git need a writable home for an arbitrary UID that has no passwd entry.
RUN setcap CAP_NET_BIND_SERVICE=+ep /usr/local/bin/frankenphp

ENV SERVER_NAME=:80 \
    HOME=/tmp \
    XDG_CONFIG_HOME=/tmp/config \
    XDG_DATA_HOME=/tmp/data \
    COMPOSER_HOME=/tmp/composer

WORKDIR /app
