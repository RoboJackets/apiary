# syntax = docker/dockerfile:1.3

FROM node:17 as frontend

RUN npm install -g npm@latest

RUN mkdir -p /app/public

# artisan is not strictly required for JS builds but it triggers some behavior inside Laravel Mix
# https://github.com/laravel-mix/laravel-mix/issues/1326#issuecomment-363975710
COPY package.json package-lock.json webpack.mix.js artisan /app/
COPY resources/ /app/resources/

WORKDIR /app/

RUN set -e && \
    set -x && \
    npm ci --no-progress && \
    npm run production --no-progress

FROM debian:bullseye-slim as backend

LABEL maintainer="developers@robojackets.org"

ENV DEBIAN_FRONTEND=noninteractive \
    COMPOSER_NO_INTERACTION=1

RUN set -e && \
    set -x && \
    apt-get update && \
    apt-get upgrade -qq --assume-yes && \
    apt-get install -qq --assume-yes \
        php7.4-fpm php7.4-mysql php7.4-gd php7.4-xml php7.4-mbstring php7.4-zip php7.4-curl php7.4-intl \
        php7.4-opcache php7.4-bcmath php7.4-ldap php7.4-uuid php7.4-sqlite sqlite3 exiftool ghostscript unzip && \
    apt-get autoremove -qq --assume-yes && \
    mkdir /app && \
    chown www-data:www-data /app

COPY --from=composer /usr/bin/composer /usr/bin/composer

COPY --chown=www-data:www-data app/ /app/app/
COPY --chown=www-data:www-data bootstrap/ /app/bootstrap/
COPY --chown=www-data:www-data config/ /app/config/
COPY --chown=www-data:www-data database/ /app/database/
COPY --chown=www-data:www-data public/ /app/public/
COPY --chown=www-data:www-data resources/ /app/resources/
COPY --chown=www-data:www-data routes/ /app/routes/
COPY --chown=www-data:www-data storage/ /app/storage/
COPY --chown=www-data:www-data artisan composer.json composer.lock server.php /app/

COPY --from=frontend --chown=www-data:www-data /app/public/ /app/public/

WORKDIR /app/

USER www-data

RUN --mount=type=secret,id=composer_auth,dst=/app/auth.json,uid=33,gid=33 \
    set -e && \
    set -x && \
    composer install --no-interaction --no-progress --no-dev --optimize-autoloader --classmap-authoritative --no-cache && \
    php artisan nova:publish && \
    php artisan horizon:publish

CMD set -e && \
    set -x && \
    php artisan passport:keys --no-interaction && \
    php artisan migrate --no-interaction && \
    php artisan serve
