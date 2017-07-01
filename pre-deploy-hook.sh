#!/bin/bash

cd "${0%/*}"

php artisan down --message="Application upgrade in progress. Please retry in a few minutes." --retry=60
