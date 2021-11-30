FROM laradock/workspace:latest-${APP_PHP_VERSION}

ARG BRANCH_NAME

RUN git checkout ${BRANCH_NAME} \
    git fetch --all \
    git reset --hard origin/main \
    php artisan key:generate \
    php artisan config:cache
