FROM php:8.2-apache

# Install MySQL extension
RUN docker-php-ext-install mysqli

# Fix Apache to listen on all interfaces
RUN sed -i 's/Listen 80/Listen 0.0.0.0:80/g' /etc/apache2/ports.conf

# Also update virtual host
RUN sed -i 's/:80/:80/g' /etc/apache2/sites-available/000-default.conf

# Copy project files
COPY . /var/www/html/

# Enable rewrite
RUN a2enmod rewrite

# Fix server name warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

EXPOSE 80