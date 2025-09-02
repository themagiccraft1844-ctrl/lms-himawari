FROM php:8.2-fpm-alpine
RUN docker-php-ext-install pdo_mysql mysqli && docker-php-ext-enable pdo_mysql mysqli
WORKDIR /var/www/html
