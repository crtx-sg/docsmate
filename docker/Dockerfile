FROM php:7.4-apache 

# Installing lib required for extensions
RUN apt-get update \
  && apt-get install -y zlib1g-dev libicu-dev g++ libzip-dev zip vim pandoc libpng-dev nodejs npm

# Installing required extensions
RUN docker-php-ext-install gd intl zip mysqli

RUN a2enmod rewrite

# COPY apache2.conf /etc/apache2/
COPY configurations/http_conf/000-default.conf /etc/apache2/sites-available/
COPY build/ /var/www/html/

RUN chmod 777 -R /var/www/html/ \
    && chown -R www-data:www-data /var/www/html/ 

EXPOSE 80
