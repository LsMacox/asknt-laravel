ARG APP_PHP_VERSION
FROM laradock/workspace:latest-${APP_PHP_VERSION}

ARG BRANCH_NAME

RUN git checkout ${BRANCH_NAME} \
    git fetch origin ${BRANCH_NAME} \
    git reset --hard origin/${BRANCH_NAME} \
    composer install --ignore-platform-reqs \
    php artisan key:generate \
    php artisan config:cache
