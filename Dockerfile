FROM php:8.2-apache

# Install MySQL extension
RUN docker-php-ext-install mysqli

# Copy your project files
COPY . /var/www/html/

# Enable .htaccess support
RUN a2enmod rewrite

# Fix Apache warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Expose default Apache port
EXPOSE 80