FROM php:8.1-fpm-alpine

ARG XDEBUG_VERSION=3.1.4

# Install dependencies.
RUN apk update && apk add autoconf make curl git libintl icu icu-dev pcre-dev g++ \
    && pecl install xdebug-$XDEBUG_VERSION \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-install pdo_mysql opcache \
    && docker-php-ext-install intl

# Install all dependencies.
# https://stackoverflow.com/questions/48169044/docker-alpine-enable-gd-jpeg-support
RUN apk add --no-cache \
        busybox-extras \
        freetype \
        freetype-dev \
        libjpeg-turbo \
        libjpeg-turbo-dev \
        libpng \
        libpng-dev
#RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ --with-png-dir=/usr/include/
#RUN docker-php-ext-install gd

# Apply our PHP configs.
COPY ./docker/php-fpm/conf.d $PHP_INI_DIR/conf.d

# Install composer.
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

WORKDIR /var/www/project

USER www-data
