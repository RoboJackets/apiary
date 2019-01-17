#!/bin/bash

cd "${0%/*}"

composer install --no-interaction --no-progress --no-suggest
php artisan migrate --no-interaction
php artisan config:clear --no-interaction
php artisan view:clear --no-interaction
php artisan route:clear --no-interaction
php artisan nova:publish --no-interaction
php artisan cache:clear --no-interaction
npm install --no-progress
npm run production --no-progress
php artisan up
php artisan horizon:terminate
