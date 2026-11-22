FROM php:8.2-apache-bookworm

RUN apt-get update \
  && apt-get install -y --no-install-recommends libzip-dev \
  && docker-php-ext-install pdo_mysql zip \
  && rm -rf /var/lib/apt/lists/*

COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/apache/php.ini /usr/local/etc/php/conf.d/99-app.ini
COPY docker/apache/servername.conf /etc/apache2/conf-available/servername.conf
RUN a2enmod rewrite \
  && a2enconf servername

WORKDIR /var/www/html
COPY index.php /var/www/html/index.php
COPY api /var/www/html/api
COPY core /var/www/html/core
COPY config /var/www/html/config
COPY controllers /var/www/html/controllers
COPY database /var/www/html/database
COPY models /var/www/html/models
COPY views /var/www/html/views
COPY public /var/www/html/public
RUN mkdir -p public/js/jocs public/uploads \
  && chown -R www-data:www-data public/js/jocs public/uploads

EXPOSE 80
