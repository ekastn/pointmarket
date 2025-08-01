FROM php:8.4-apache

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y     git     unzip     libzip-dev     libonig-dev     libxml2-dev     libpng-dev     && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy the application code
COPY . /var/www/html/frontend

# Set the Apache document root to the 'public' directory
RUN sed -i -e 's/DocumentRoot \/var\/www\/html/DocumentRoot \/var\/www\/html\/frontend\/public/g' /etc/apache2/sites-available/000-default.conf \
    && a2enmod rewrite

# Create storage directory and set permissions
RUN mkdir -p /var/www/html/frontend/storage     && chown -R www-data:www-data /var/www/html/frontend     && chmod -R 755 /var/www/html/frontend/storage

# Install Composer dependencies
WORKDIR /var/www/html/frontend
RUN composer install --no-dev --optimize-autoloader

# Expose port 80
EXPOSE 80

# Start Apache server
CMD ["apache2-foreground"]
