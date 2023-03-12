FROM composer:latest
WORKDIR /code
COPY ./src /code
COPY ./php.ini /usr/local/etc/php/conf.d/docker-php-test-2.ini
