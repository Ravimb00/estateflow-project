FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpng-dev \
    && docker-php-ext-install mysqli gd

COPY . /var/www/html/

EXPOSE 80