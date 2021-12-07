#!/usr/bin/env bash

set -a

FILE_ENV='.env'
if [ ! -f "$FILE_ENV" ]; then
    cp .env.example .env
fi

export $(cat .env | grep -v '#' | awk '/=/ {print $1}')

if [ -z "$PHP_VERSION" ]; then PHP_VERSION='7.4'; fi
if [ -z "$BUILD_GIT_BRANCH" ]; then BUILD_GIT_BRANCH='main'; fi

git fetch origin ${BUILD_GIT_BRANCH}
git reset --hard origin/${BUILD_GIT_BRANCH}

composer install

chmod -R 777 storage
php artisan key:generate
php artisan config:cache
php artisan migrate
php artisan storage:link
