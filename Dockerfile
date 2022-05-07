# syntax = docker/dockerfile:1.4

ARG base_image="debian:bullseye-slim"

FROM node:17 as frontend

RUN npm install -g npm@latest

RUN mkdir -p /app/public

# artisan is not strictly required for JS builds but it triggers some behavior inside Laravel Mix
# https://github.com/laravel-mix/laravel-mix/issues/1326#issuecomment-363975710
COPY package.json package-lock.json webpack.mix.js artisan /app/
COPY resources/ /app/resources/

WORKDIR /app/

RUN set -eux && \
    npm ci --no-progress && \
    npm run production --no-progress

FROM ${base_image} as backend

LABEL maintainer="developers@robojackets.org"

ENV DEBIAN_FRONTEND=noninteractive \
    COMPOSER_NO_INTERACTION=1 \
    HOME=/tmp

RUN set -eux && \
    apt-get update && \
    apt-get upgrade -qq --assume-yes && \
    apt-get install -qq --assume-yes \
        php7.4-fpm php7.4-mysql php7.4-gd php7.4-xml php7.4-mbstring php7.4-zip php7.4-curl php7.4-intl \
        php7.4-opcache php7.4-bcmath php7.4-ldap php7.4-uuid php7.4-sqlite sqlite3 exiftool ghostscript unzip libfcgi-bin default-mysql-client && \
    apt-get autoremove -qq --assume-yes && \
    mkdir /app && \
    chown www-data:www-data /app && \
    sed -i '/error_log/c\error_log = /local/error.log' /etc/php/7.4/fpm/php-fpm.conf

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

RUN --mount=type=secret,id=composer_auth,dst=/app/auth.json,uid=33,gid=33,required=true \
    set -eux && \
    composer install --no-interaction --no-progress --no-dev --optimize-autoloader --classmap-authoritative --no-cache && \
    php artisan nova:publish && \
    php artisan horizon:publish && \
    sed -i '/HTTPS_ONLY_COOKIES/c\true,' /app/vendor/subfission/cas/src/Subfission/Cas/CasManager.php
