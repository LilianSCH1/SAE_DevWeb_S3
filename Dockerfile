FROM php:8.2-apache

# Dépendances
RUN apt-get update && apt-get install -y \
    libfreetype6-dev libjpeg62-turbo-dev libpng-dev libzip-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install mysqli pdo_mysql gd zip exif

# Apache
RUN a2enmod rewrite

# PHP debug
RUN echo 'display_errors=On' > /usr/local/etc/php/conf.d/debug.ini

EXPOSE 80
CMD ["apache2-foreground"]
