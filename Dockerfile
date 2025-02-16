FROM php:7.4-apache

# Install necessary PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Copy website code into the container
COPY . /var/www/html/

# Expose port 80
EXPOSE 80

# Run Apache Server
CMD ["apache2-foreground"]
