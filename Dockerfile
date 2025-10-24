# Use a more stable PHP 8.2 Apache image
FROM php:8.2.11-apache

# Install required extensions and tools
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-install pdo_mysql mysqli gd zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www/html

# Copy only composer files first for better caching
COPY composer.* ./

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install dependencies (no dev dependencies for production)
RUN if [ -f "composer.json" ]; then \
    composer install --no-dev --optimize-autoloader --no-interaction --no-progress; \
    fi

# Copy the rest of the application
COPY . .

# Set file permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && [ -d "/var/www/html/bootstrap/cache" ] && chmod -R 755 /var/www/html/bootstrap/cache || true

# Configure Apache
RUN echo 'ServerName localhost' >> /etc/apache2/apache2.conf \
    && echo '<VirtualHost *:80>
    DocumentRoot /var/www/html
    <Directory "/var/www/html">
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
