#!/bin/bash

cd "${0%/*}"

composer install --no-interaction --no-progress --no-suggest --no-dev --optimize-autoloader --classmap-authoritative
php artisan migrate --no-interaction
php artisan config:cache --no-interaction
php artisan view:clear --no-interaction
php artisan route:clear --no-interaction
php artisan nova:publish --no-interaction
php artisan horizon:assets --no-interaction
php artisan cache:clear --no-interaction

if [ -f ".last_deployment_hash" ]; then
    LAST_DEPLOYMENT=$(cat .last_deployment_hash)
else
    LAST_DEPLOYMENT=
fi

THIS_DEPLOYMENT=$(git rev-parse HEAD)

if [ "$LAST_DEPLOYMENT" == "" ] || [ "$THIS_DEPLOYMENT" == "$LAST_DEPLOYMENT" ] || git diff --name-only $LAST_DEPLOYMENT $THIS_DEPLOYMENT | grep -q '^package-lock\.json$'; then
    npm ci --no-progress
fi

if [ "$LAST_DEPLOYMENT" == "" ] || [ "$THIS_DEPLOYMENT" == "$LAST_DEPLOYMENT" ] || git diff --name-only $LAST_DEPLOYMENT $THIS_DEPLOYMENT | grep -q '^package-lock\.json$' || git diff --name-only $LAST_DEPLOYMENT $THIS_DEPLOYMENT | grep -q '.+\.[js|vue]$'; then
    npm run production --no-progress
fi

php artisan up
php artisan horizon:terminate
php artisan bugsnag:deploy --repository "https://github.com/RoboJackets/apiary" --revision $(git rev-parse HEAD) --builder "rj-dc-00"

./resume-monitoring.sh

date
