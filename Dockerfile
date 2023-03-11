FROM composer:latest
WORKDIR /code
COPY ./src /code
RUN composer require