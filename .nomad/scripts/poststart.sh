# Meilisearch does not serve HTTP requests while importing a dump on startup, so wait for it to become available.
attempts=0
until curl --silent --fail --max-time 5 "${MEILISEARCH_HOST}/health" > /dev/null
do
    attempts=$((attempts + 1))
    if [ "$attempts" -ge 600 ]
    then
        echo "Meilisearch did not become available after $attempts attempts; giving up." >&2
        exit 1
    fi
    echo "Waiting for Meilisearch to be available (attempt $attempts)..."
    sleep 1
done

php artisan scout:sync-index-settings --no-interaction --verbose
php artisan scout:import-all --no-interaction --verbose
php artisan meilisearch:dump --no-interaction --verbose
