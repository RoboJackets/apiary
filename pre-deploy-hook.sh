#!/bin/bash

date

cd "${0%/*}"

./pause-monitoring.sh

php artisan down --retry=60 || true

git rev-parse HEAD > .last_deployment_hash
