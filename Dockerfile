# syntax = docker/dockerfile:1.4

ARG base_image="debian:bookworm-slim"

FROM scratch as frontend-source

# artisan is not strictly required for JS builds but it triggers some behavior inside Laravel Mix
# https://github.com/laravel-mix/laravel-mix/issues/1326#issuecomment-363975710
COPY --link package.json package-lock.json webpack.mix.js artisan /app/
COPY --link resources/ /app/resources/
COPY --link public/ /app/public/

FROM node:18 as frontend

RUN npm install -g npm@latest

COPY --link --from=frontend-source /app/ /app/

WORKDIR /app/

RUN set -eux && \
    npm ci --no-progress && \
    npm run production --no-progress

FROM scratch as backend-source

COPY --link app/ /app/app/
COPY --link bootstrap/ /app/bootstrap/
COPY --link config/ /app/config/
COPY --link database/ /app/database/
COPY --link resources/ /app/resources/
COPY --link routes/ /app/routes/
COPY --link storage/ /app/storage/
COPY --link lang/ /app/lang/
COPY --link artisan composer.json composer.lock /app/
COPY --link --from=frontend /app/public/ /app/public/

FROM ${base_image} as backend-uncompressed

LABEL maintainer="developers@robojackets.org"

ENV DEBIAN_FRONTEND=noninteractive \
    COMPOSER_NO_INTERACTION=1 \
    HOME=/tmp

RUN set -eux && \
    apt-get update && \
    apt-get upgrade -qq --assume-yes && \
    apt-get install -qq --assume-yes \
        php8.1-fpm php8.1-mysql php8.1-gd php8.1-xml php8.1-mbstring php8.1-zip php8.1-curl php8.1-intl \
        php8.1-opcache php8.1-bcmath php8.1-ldap php8.1-uuid php8.1-sqlite sqlite3 exiftool ghostscript \
        unzip libfcgi-bin default-mysql-client zopfli php8.1-redis && \
    apt-get autoremove -qq --assume-yes && \
    mkdir /app && \
    chown www-data:www-data /app && \
    sed -i '/error_log/c\error_log = /local/error.log' /etc/php/8.1/fpm/php-fpm.conf && \
    sed -i '/expose_php/c\expose_php = Off' /etc/php/8.1/fpm/php.ini && \
    sed -i '/expose_php/c\expose_php = Off' /etc/php/8.1/cli/php.ini && \
    sed -i '/allow_url_fopen/c\allow_url_fopen = Off' /etc/php/8.1/fpm/php.ini && \
    sed -i '/allow_url_fopen/c\allow_url_fopen = Off' /etc/php/8.1/cli/php.ini && \
    sed -i '/allow_url_include/c\allow_url_include = Off' /etc/php/8.1/fpm/php.ini && \
    sed -i '/allow_url_include/c\allow_url_include = Off' /etc/php/8.1/cli/php.ini

COPY --link --from=composer /usr/bin/composer /usr/bin/composer

COPY --link --from=backend-source --chown=www-data:www-data /app/ /app/

WORKDIR /app/

USER www-data

RUN --mount=type=secret,id=composer_auth,dst=/app/auth.json,uid=33,gid=33,required=true \
    set -eux && \
    composer install --no-interaction --no-progress --no-dev --optimize-autoloader --classmap-authoritative --no-cache && \
    php artisan nova:publish && \
    php artisan horizon:publish && \
    sed -i '/HTTPS_ONLY_COOKIES/c\true,' /app/vendor/subfission/cas/src/Subfission/Cas/CasManager.php && \
    sed -i '/"\$1\\n\$2"/c\\' /app/vendor/mrclay/minify/lib/Minify/HTML.php;

# This target is the default, but skipped during pull request builds and in our recommended local build invocation
# precompressed_assets var on the Nomad job must match whether this stage ran or not
FROM backend-uncompressed as backend-compressed

RUN set -eux && \
    cd /app/public/ && \
    find . -type f -size +0 | while read file; do \
        filename=$(basename -- "$file"); \
        extension="${filename##*.}"; \
        if [ "$extension" = "css" ] || [ "$extension" = "js" ] || [ "$extension" = "svg" ]; then \
          zopfli --gzip -v --i10 "$file"; \
          touch "$file".gz "$file"; \
        elif [ "$extension" = "png" ]; then \
          zopflipng -m -y --lossy_transparent --lossy_8bit --filters=01234mepb --iterations=5 "$file" "$file"; \
        fi; \
    done;
