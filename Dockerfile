FROM composer:2 AS composer
FROM php:8.1-cli-alpine AS php-cli

RUN mkdir -p /app

COPY ./bin /app/bin
COPY ./config /app/config
COPY ./src /app/src
COPY ./composer.* /app/
RUN chmod +x /app/bin/console

WORKDIR /app

ENV COMPOSER_ALLOW_SUPERUSER=1
COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-scripts

CMD [ "php", "-a" ]
