if [ ${APP_ENV} = "sandbox" ]
then
    php artisan passport:keys --no-interaction --verbose
    export APP_KEY=$(php artisan key:generate --show --verbose)
    php artisan migrate --no-interaction --force --verbose
    php artisan tinker --no-interaction --verbose --execute "\App\Models\OAuth2Client::create(['id' => '95158a03-4ce9-489d-9550-05655f9f27eb', 'redirect_uris' => ['org.robojackets.apiary://oauth'], 'name' => 'Android App', 'grant_types' => ['authorization_code', 'refresh_token'], 'revoked' => false]);"
    export PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=$(php artisan tinker --no-interaction --verbose --execute "echo ((new \\Laravel\\Passport\\ClientRepository())->createPersonalAccessGrantClient('Personal Access Client', 'users'))->plainSecret")
    export PASSPORT_PERSONAL_ACCESS_CLIENT_ID=$(php artisan tinker --no-interaction --verbose --execute "echo \\Laravel\\Passport\\Passport::client()->where('name', 'Personal Access Client')->sole()->getKey()")
fi
php artisan config:validate --no-interaction --verbose
php artisan config:cache --no-interaction --verbose
php artisan view:cache --no-interaction --verbose
php artisan event:cache --no-interaction --verbose
php artisan route:cache --no-interaction --verbose
php artisan cache:clear --no-interaction --verbose
php artisan responsecache:clear --no-interaction --verbose
php artisan migrate --no-interaction --force --verbose

if [ ${APP_ENV} != "sandbox" ]
then
    export SKIP_PHPSTAN_CHECKS=true
    if ! php artisan ping --no-interaction --verbose
    then
        export SKIP_HTTP_CHECKS=true
    fi
    php artisan config:cache --no-interaction --verbose
fi

mkdir --parents /assets/${NOMAD_JOB_NAME}/
cp --recursive --verbose public/* /assets/${NOMAD_JOB_NAME}/

php artisan notify-indexing-in-progress --no-interaction --verbose
