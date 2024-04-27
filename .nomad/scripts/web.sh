if [ ${APP_ENV} = "sandbox" ]
then
    php artisan passport:keys --no-interaction --verbose
    export APP_KEY=$(php artisan key:generate --show --verbose)
    php artisan migrate --no-interaction --force --verbose
    php artisan tinker --no-interaction --verbose --execute "\App\Models\OAuth2Client::create(['id' => '95158a03-4ce9-489d-9550-05655f9f27eb', 'redirect' => 'org.robojackets.apiary://oauth', 'name' => 'Android App', 'personal_access_client' => false, 'password_client' => false, 'revoked' => false]);"
    export BUZZAPI_APP_ID=$(openssl rand -hex 32)
    export BUZZAPI_APP_PASSWORD=$(openssl rand -hex 32)
fi
php artisan config:cache --no-interaction --verbose
php artisan view:cache --no-interaction --verbose
php artisan event:cache --no-interaction --verbose
php artisan route:cache --no-interaction --verbose
exec php-fpm8.3 --force-stderr --nodaemonize --fpm-config /etc/php/8.3/fpm/php-fpm.conf
