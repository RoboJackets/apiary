php artisan scout:sync-index-settings --no-interaction --verbose || true
if ! php artisan scout:indexes-ready --no-interaction --verbose
then
    php artisan scout:import-all --no-interaction --verbose
else
    php artisan scout:import \\App\\Models\\Airport --no-interaction --verbose
fi
