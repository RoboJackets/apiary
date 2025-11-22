# syntax = docker/dockerfile:1.19

FROM python:3.13-bookworm AS docs-source

COPY --link docs/ /docs/

WORKDIR /docs/

SHELL ["/bin/bash", "-c"]

RUN set -euxo pipefail && \
    curl -sSL https://install.python-poetry.org | python3 - && \
    /root/.local/bin/poetry install --no-interaction && \
    /root/.local/bin/poetry run sphinx-build -M dirhtml "." "_build"

FROM node:24 AS docs-minification

COPY --link --from=docs-source /docs/_build/dirhtml/ /docs/

RUN set -eux && \
    npm install -g npm@latest && \
    npx html-minifier --input-dir /docs/ --output-dir /docs/ --file-ext html --collapse-whitespace --collapse-inline-tag-whitespace --minify-css --minify-js --minify-urls ROOT_PATH_RELATIVE --remove-comments --remove-empty-attributes --conservative-collapse && \
    find /docs/ -type f -size +0 | while read file; do \
        filename=$(basename -- "$file"); \
        extension="${filename##*.}"; \
        if [ "$extension" = "js" ]; then \
            npx terser "$file" --compress --output "$file"; \
        fi; \
        if [ "$extension" = "css" ]; then \
            npx clean-css-cli "$file" -O2 --output "$file"; \
        fi; \
        if [ "$extension" = "map" ]; then \
            rm -f "$file"; \
        fi; \
    done;

FROM scratch AS frontend-source

# artisan is not strictly required for JS builds but it triggers some behavior inside Laravel Mix
# https://github.com/laravel-mix/laravel-mix/issues/1326#issuecomment-363975710
COPY --link package.json package-lock.json webpack.mix.js artisan /app/
COPY --link resources/ /app/resources/
COPY --link public/ /app/public/

FROM node:24 AS nova-components

COPY --link /nova-components/ /nova-components/

WORKDIR /nova-components/ClientIdAndSecretModal/

RUN set -eux && \
    npm install -g npm@latest && \
    npm install --no-progress && \
    npm run production --no-progress

WORKDIR /nova-components/PersonalAccessTokenModal/

RUN set -eux && \
    npm install -g npm@latest && \
    npm install --no-progress && \
    npm run production --no-progress

FROM node:24 AS frontend

COPY --link --from=frontend-source /app/ /app/

WORKDIR /app/

RUN set -eux && \
    npm install -g npm@latest && \
    npm ci --no-progress && \
    npm run production --no-progress

FROM scratch AS backend-source

COPY --link app/ /app/app/
COPY --link bootstrap/ /app/bootstrap/
COPY --link config/ /app/config/
COPY --link config-validation/ /app/config-validation/
COPY --link database/ /app/database/
COPY --link resources/ /app/resources/
COPY --link routes/ /app/routes/
COPY --link artisan composer.json composer.lock /app/
COPY --link --from=frontend /app/public/ /app/public/
COPY --link nova-components/ /app/nova-components/
COPY --link --from=nova-components /nova-components/ClientIdAndSecretModal/dist/ /app/nova-components/ClientIdAndSecretModal/dist/
COPY --link --from=nova-components /nova-components/PersonalAccessTokenModal/dist/ /app/nova-components/PersonalAccessTokenModal/dist/
COPY --link --from=docs-minification /docs/ /app/public/docs/

FROM ubuntu:noble AS backend-uncompressed

LABEL maintainer="developers@robojackets.org"

ENV DEBIAN_FRONTEND=noninteractive \
    COMPOSER_NO_INTERACTION=1 \
    HOME=/tmp

RUN set -eux && \
    apt-get update && \
    apt-get upgrade -qq --assume-yes && \
    apt-get install -qq --assume-yes \
        php8.3-fpm php8.3-mysql php8.3-gd php8.3-xml php8.3-mbstring php8.3-zip php8.3-curl php8.3-intl \
        php8.3-opcache php8.3-bcmath php8.3-ldap php8.3-uuid php8.3-sqlite sqlite3 exiftool ghostscript \
        unzip libfcgi-bin default-mysql-client zopfli php8.3-redis file && \
    apt-get autoremove -qq --assume-yes && \
    mkdir /app && \
    chown www-data:www-data /app && \
    sed -i '/pid/c\\' /etc/php/8.3/fpm/php-fpm.conf && \
    sed -i '/systemd_interval/c\systemd_interval = 0' /etc/php/8.3/fpm/php-fpm.conf && \
    sed -i '/error_log/c\error_log = /local/error.log' /etc/php/8.3/fpm/php-fpm.conf && \
    sed -i '/expose_php/c\expose_php = Off' /etc/php/8.3/fpm/php.ini && \
    sed -i '/expose_php/c\expose_php = Off' /etc/php/8.3/cli/php.ini && \
    sed -i '/allow_url_fopen/c\allow_url_fopen = Off' /etc/php/8.3/fpm/php.ini && \
    sed -i '/allow_url_fopen/c\allow_url_fopen = Off' /etc/php/8.3/cli/php.ini && \
    sed -i '/allow_url_include/c\allow_url_include = Off' /etc/php/8.3/fpm/php.ini && \
    sed -i '/allow_url_include/c\allow_url_include = Off' /etc/php/8.3/cli/php.ini

COPY --link --from=composer /usr/bin/composer /usr/bin/composer

COPY --link --from=backend-source --chown=33:33 /app/ /app/

WORKDIR /app/

USER www-data

RUN --mount=type=secret,id=composer_auth,dst=/app/auth.json,uid=33,gid=33,required=true \
    set -eux && \
    mkdir --parents /app/storage/app/ /app/storage/framework/cache/ /app/storage/framework/sessions/ /app/storage/framework/testing/ /app/storage/framework/views/ /app/storage/logs/ && \
    composer check-platform-reqs --lock --no-dev && \
    composer install --no-interaction --no-progress --no-dev --optimize-autoloader --classmap-authoritative --no-cache && \
    php artisan nova:publish && \
    sed -i '/"\$1\\n\$2"/c\\' /app/vendor/mrclay/minify/lib/Minify/HTML.php && \
    chmod 664 /app/bootstrap/app.php /app/public/index.php && \
    chmod 775 /app/bootstrap/cache/

# This target is the default, but skipped during pull request builds and in our recommended local build invocation
# precompressed_assets var on the Nomad job must match whether this stage ran or not
FROM backend-uncompressed AS backend-compressed

RUN set -eux && \
    cd /app/public/ && \
    find . -type f -size +0 | while read file; do \
        filename=$(basename -- "$file"); \
        extension="${filename##*.}"; \
        if [ "$extension" = "css" ] || [ "$extension" = "js" ] || [ "$extension" = "svg" ] || [ "$extension" = "html" ]; then \
          zopfli --gzip -v --i10 "$file"; \
          touch "$file".gz "$file"; \
        elif [ "$extension" = "png" ]; then \
          zopflipng -m -y --lossy_transparent --lossy_8bit --filters=01234mepb --iterations=5 "$file" "$file"; \
        fi; \
    done;
