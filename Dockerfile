ARG APP_PHP_VERSION
FROM laradock/workspace:latest-${APP_PHP_VERSION}

ARG BRANCH_NAME

RUN git checkout ${BRANCH_NAME} \
    git fetch origin ${BRANCH_NAME}

RUN git reset --hard origin/${BRANCH_NAME}

RUN composer install --ignore-platform-reqs \
    php artisan key:generate \
    php artisan config:cache
