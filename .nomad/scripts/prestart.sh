php artisan config:cache --no-interaction --verbose
php artisan view:cache --no-interaction --verbose
php artisan event:cache --no-interaction --verbose
php artisan route:cache --no-interaction --verbose
php artisan cache:clear --no-interaction --verbose
php artisan migrate --no-interaction --force --verbose

if [ ${APP_ENV} != "google-play-review" ]
then
    if [ php artisan ping ]
    then
        export SKIP_HTTP_CHECKS=true
    fi
    php artisan enlightn --details --show-exceptions
fi

mkdir --parents /assets/${NOMAD_JOB_NAME}/
cp --recursive --verbose public/* /assets/${NOMAD_JOB_NAME}/

if [ ${PERSIST_RESUMES} = "false" ] && [ ${DB_CONNECTION} = "mysql" ]
then
    mysql --execute="update users set resume_date=null"
fi

if [ ${SCOUT_DRIVER} = "meilisearch" ]
then
    php artisan meilisearch:update-index-settings --no-interaction --verbose --only-return-id || true
fi
