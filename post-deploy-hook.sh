#!/bin/bash

cd "${0%/*}"

composer install --no-interaction
php artisan migrate --no-interaction
php artisan config:clear --no-interaction
npm install
npm run production
php artisan up
