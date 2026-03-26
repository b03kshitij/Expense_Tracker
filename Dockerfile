FROM php:8.2-apache

RUN docker-php-ext-install mysqli

RUN a2enmod rewrite

# VERY IMPORTANT: set correct working dir
WORKDIR /var/www/html

COPY . .

# Fix permissions
RUN chown -R www-data:www-data /var/www/html

# Ensure index.php is default
RUN echo "DirectoryIndex index.php" > /etc/apache2/conf-available/custom.conf \
    && a2enconf custom