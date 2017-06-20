#!/bin/bash

cd /var/www/apiary

composer install --no-interaction
php artisan migrate --no-interaction
