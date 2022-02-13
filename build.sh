#!/usr/bin/env bash

set -a

# Create .env file if don`t exist
FILE_ENV='.env'
if [ ! -f "$FILE_ENV" ]; then
    cp .env.example .env
fi

# Export all variables of .env file
export $(cat .env | grep -v '#' | awk '/=/ {print $1}')

if [ -z "$PHP_VERSION" ]; then PHP_VERSION='7.4'; fi
if [ -z "$BUILD_GIT_BRANCH" ]; then BUILD_GIT_BRANCH='main'; fi

# Turn on maintenance mode
php artisan down || true

# Pull the latest changes from the git repository
# git reset --hard
# git clean -df
# git fetch origin ${BUILD_GIT_BRANCH}
# git reset --hard origin/${BUILD_GIT_BRANCH}
git pull origin $BUILD_GIT_BRANCH

# Install/update composer dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

chmod -R 777 storage

# Run database migrations
php artisan migrate --force

# Clear caches
php artisan cache:clear

# Clear and cache routes
php artisan route:cache

# Clear and cache config
php artisan config:cache

# Clear and cache views
php artisan view:cache

# Turn off maintenance mode
php artisan up
