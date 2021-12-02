#!/usr/bin/env bash

set -a

FILE_ENV='.env'
if [ ! -f "$FILE_ENV" ]; then
    cp .env.example .env
fi

if [ -f .env ]; then
    # Load Environment Variables
    export $(cat .env | grep -v '#' | awk '/=/ {print $1}')
fi

if [ -z "$PHP_VERSION" ]; then PHP_VERSION='7.4'; fi
if [ -z "$BUILD_GIT_BRANCH" ]; then BUILD_GIT_BRANCH='main'; fi

COMPOSER_ARGS='-n --ignore-platform-reqs'

git fetch origin ${BUILD_GIT_BRANCH}
git reset --hard origin/${BUILD_GIT_BRANCH}

DIR_VENDOR='vendor'
if [ -d "$DIR_VENDOR" ];
then
    composer update ${COMPOSER_ARGS}
else
    composer install --no-cache ${COMPOSER_ARGS}
fi

php artisan key:generate
php artisan migrate
php artisan config:cache
php artisan storage:link
