FROM php:8.3-fpm-alpine AS base

RUN apk add --no-cache --update --virtual build-dependencies \
    icu-dev

RUN docker-php-ext-install pdo_mysql

FROM php:8.3-fpm-alpine AS production-stage

COPY --from=base /usr/local/lib/php/extensions/no-debug-non-zts-20230831/pdo_mysql.so /usr/local/lib/php/extensions/no-debug-non-zts-20230831/pdo_mysql.so

COPY --from=base /usr/local/etc/php/conf.d/docker-php-ext-pdo_mysql.ini /usr/local/etc/php/conf.d/docker-php-ext-pdo_mysql.ini
RUN docker-php-ext-enable pdo_mysql

ARG UID
ARG GID
ARG USER=www-data
ARG GROUP=www-data

RUN apk add --no-cache \
    shadow

RUN groupmod -g ${GID} ${GROUP} && \
    usermod -u ${UID} -g ${GID} ${GROUP}

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY --chown=${USER}:${GROUP} . /var/www

WORKDIR /var/www

USER ${USER}
