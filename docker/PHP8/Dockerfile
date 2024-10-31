FROM php:8.3.3-fpm-alpine3.18

RUN apk add --no-cache --update \
  bzip2-dev \
  enchant2-dev \
  gmp-dev \
  imap-dev \
  icu-dev \
  openldap-dev \
  freetds-dev \
  libxml2-dev \
  tidyhtml-dev  \
  libxslt-dev \
  libzip-dev \
  musl-dev \
  jpeg-dev \
  libpng-dev \
  oniguruma-dev \
  freetype  \
  libpng  \
  libjpeg-turbo  \
  freetype-dev  \
  libjpeg-turbo-dev \
  bash \
  openrc

RUN docker-php-ext-configure intl --enable-intl
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

RUN docker-php-ext-install -j$(nproc) gd \
    pdo \
    mysqli  \
    pdo_mysql  \
    intl  \
    mbstring  \
    zip  \
    pcntl \
    exif  \
    opcache \
    soap \
    && docker-php-source delete

#Installing composer
RUN curl -sS https://getcomposer.org/installer -o composer-setup.php
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer
RUN rm -rf composer-setup.php

RUN rm -rf /tmp/* /var/cache/apk/*

WORKDIR /app

ENV user=docker

RUN adduser -D -u 1000 ${user}
RUN addgroup ${user} www-data

RUN chown -R www-data:www-data /app

RUN chmod 777 -R /tmp && chmod o+t -R /tmp
RUN chmod 777 -R /var/log && chmod o+t -R /var/log
RUN chmod 777 -R /var/run && chmod o+t -R /var/run

RUN mv /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini

COPY ./conf.d /usr/local/etc/php/conf.d

USER ${user}
