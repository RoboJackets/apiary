#!/bin/bash

cd /var/www/apiary

composer install
php artisan migrate --no-interaction
