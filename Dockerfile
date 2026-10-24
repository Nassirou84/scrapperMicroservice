# syntax=docker/dockerfile:1

# ---- base: PHP runtime (FrankenPHP = Caddy + PHP) ----
FROM dunglas/frankenphp:1-php8.4-alpine AS base

WORKDIR /app

RUN install-php-extensions \
        @composer \
        opcache \
        pdo_pgsql \
        sockets \
        zip

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    SERVER_NAME=:80

# ---- dev: includes dev dependencies, source is bind-mounted by compose ----
FROM base AS dev

COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-interaction --no-scripts --no-autoloader --prefer-dist

COPY . .
RUN composer dump-autoload --no-interaction

ENV APP_ENV=dev

# ---- prod: no dev dependencies, optimized autoloader, warmed cache ----
FROM base AS prod

COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --no-autoloader --prefer-dist

COPY . .
RUN composer dump-autoload --no-dev --classmap-authoritative --no-interaction \
    && mkdir -p var \
    && APP_ENV=prod APP_SECRET=build \
       DATABASE_URL="postgresql://build:build@localhost:5432/build?serverVersion=16&charset=utf8" \
       php bin/console cache:clear \
    && chown -R www-data:www-data var

ENV APP_ENV=prod \
    APP_DEBUG=0

# Dev + prod both default to the web server; override `command` for workers.
