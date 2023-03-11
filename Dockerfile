FROM composer:latest
WORKDIR /code/public
COPY ./src /code
RUN composer require
EXPOSE 8888