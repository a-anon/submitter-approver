FROM php:5.6-fpm

RUN DEBIAN_FRONTEND=noninteactive apt-get -y update && apt-get install -y git

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /site

VOLUME /site
