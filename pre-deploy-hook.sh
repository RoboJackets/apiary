#!/bin/bash

cd "${0%/*}"

php artisan down --message="An app upgrade is in progress. Please try again in a few minutes." --retry=60

git rev-parse HEAD > .last_deployment_hash
