FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN a2enmod rewrite

# Set document root ke /var/www/html
WORKDIR /var/www/html

# Copy SELURUH project itera4_ ke container
COPY ../ /var/www/html/

RUN chown -R www-data:www-data /var/www/html
