# Use a pre-built PHP-Apache image with common extensions
FROM webdevops/php-apache:8.2

# Install additional PHP extensions
RUN docker-php-ext-install pdo_mysql gd zip

# Enable Apache modules
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy only composer files first for better caching
COPY composer.* ./

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

# Configure Apache to use PORT environment variable
ENV WEB_DOCUMENT_ROOT=/var/www/html
ENV APACHE_RUN_USER=www-data
ENV APACHE_RUN_GROUP=www-data

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["/start.sh"]
