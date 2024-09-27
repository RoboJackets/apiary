if [ ${APP_ENV} = "sandbox" ]
then
    php artisan passport:keys --no-interaction --verbose
    export APP_KEY=$(php artisan key:generate --show --verbose)
    php artisan migrate --no-interaction --force --verbose
    php artisan tinker --no-interaction --verbose --execute "\App\Models\OAuth2Client::create(['id' => '95158a03-4ce9-489d-9550-05655f9f27eb', 'redirect' => 'org.robojackets.apiary://oauth', 'name' => 'Android App', 'personal_access_client' => false, 'password_client' => false, 'revoked' => false]);"
    export PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=$(php artisan tinker --no-interaction --verbose --execute "echo ((new \\Laravel\\Passport\\ClientRepository())->createPersonalAccessClient(null, 'Personal Access Client', 'http://localhost'))->plainSecret")
    export PASSPORT_PERSONAL_ACCESS_CLIENT_ID=$(php artisan tinker --no-interaction --verbose --execute "echo \\Laravel\\Passport\\Passport::client()->sole()->getKey()")
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
    php artisan enlightn --details --show-exceptions --no-interaction --verbose
fi

mkdir --parents /assets/${NOMAD_JOB_NAME}/
cp --recursive --verbose public/* /assets/${NOMAD_JOB_NAME}/

if [ ${SCOUT_DRIVER} = "meilisearch" ]
then
    php artisan scout:sync-index-settings --no-interaction --verbose || true
    php artisan scout:import \\App\\Models\\Airport
fi
