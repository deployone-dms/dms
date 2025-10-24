# Use PHP 8.2 with Apache
FROM php:8.2-apache

# Install required PHP extensions and tools
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Enable Apache modules
RUN a2enmod rewrite headers

# Set working directory
WORKDIR /var/www/html

# Copy only composer files first to leverage Docker cache
COPY composer.* ./

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install dependencies (no dev dependencies for production)
RUN if [ -f "composer.json" ]; then \
    composer install --no-dev --optimize-autoloader --no-interaction; \
    fi

# Copy the rest of the application
COPY . .

# Set file permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && [ -d "/var/www/html/bootstrap/cache" ] && chmod -R 755 /var/www/html/bootstrap/cache || true

# Configure Apache to use PORT environment variable
RUN echo 'Listen 8080' > /etc/apache2/ports.conf
RUN echo 'Listen 80' >> /etc/apache2/ports.conf

# Create Apache configuration
RUN echo '<VirtualHost *:8080>\
    DocumentRoot /var/www/html\
    <Directory "/var/www/html">\
        Options Indexes FollowSymLinks\
        AllowOverride All\
        Require all granted\
    </Directory>\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Expose the port the app runs on
EXPOSE 8080

# Start Apache
CMD ["apache2-foreground"]
