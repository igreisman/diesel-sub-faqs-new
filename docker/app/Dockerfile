FROM php:8.2-apache

RUN set -eux; \
    apt-get update; \
    apt-get install -y libjpeg-dev libpng-dev libwebp-dev libfreetype6-dev; \
    docker-php-ext-configure gd --with-jpeg --with-freetype --with-webp; \
    docker-php-ext-install gd pdo_mysql; \
    a2enmod rewrite headers

COPY docker/app/vhost.conf /etc/apache2/sites-available/000-default.conf
