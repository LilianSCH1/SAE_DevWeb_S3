FROM php:8.2-apache

# Dépendances GD/MySQLi
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# GD configure (sans --with-png, PHP 8.2 natif)
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    mysqli pdo_mysql gd zip exif

# Apache rewrite + permissions
RUN a2enmod rewrite \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
