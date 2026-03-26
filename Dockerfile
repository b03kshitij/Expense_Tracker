FROM php:8.2-apache

RUN docker-php-ext-install mysqli

RUN a2enmod rewrite

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

RUN echo "DirectoryIndex index.php" >> /etc/apache2/apache2.conf