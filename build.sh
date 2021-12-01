#!/bin/bash

set -a

FILE_ENV='.env'
if [ ! -f "$FILE_ENV" ]; then
    cp .env.example .env
fi

if [ -f .env ]; then
    # Load Environment Variables
    export $(cat .env | grep -v '#' | awk '/=/ {print $1}')
fi

if [ -n "$PHP_VERSION" ]; then PHP_VERSION='7.4'; fi
if [ -n "$BUILD_GIT_BRANCH" ]; then BUILD_GIT_BRANCH='main'; fi
if [ -z "$GITHUB_USERNAME" ]; then
    echo 'the GITHUB_USERNAME variable was not found in the .env file'
    exit 0
fi
if [ -z "$GITHUB_ACCESS_TOKEN" ]; then
    echo 'the GITHUB_ACCESS_TOKEN variable was not found in the .env file'
    exit 0
fi

COMPOSER_WORK_DIR=./
COMPOSER_ARGS='-n --working-dir='"${COMPOSER_WORK_DIR}"' --ignore-platform-reqs'

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
