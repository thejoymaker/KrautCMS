# Use the official PHP image as the base image
FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libpng16-16 \
    libjpeg62-turbo \
    libfreetype6-dev \
    libonig-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mbstring zip pdo pdo_mysql \
    && a2enmod rewrite

# Set the working directory
WORKDIR /var/www/html

# Copy application code
# COPY . /var/www/html
COPY composer.json composer.lock /var/www/html/
COPY public /var/www/html/public/
# COPY public/.htaccess /var/www/html/public/.htaccess

COPY .env /var/www/html/.env
COPY User /var/www/html/User/
COPY Kraut /var/www/html/Kraut/

# Explicitly copy .htaccess
COPY public/.htaccess /var/www/html/public/.htaccess

# Update Apache configuration to use /var/www/html/public as the DocumentRoot
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions for writable directories
RUN mkdir -p /var/www/html/Log /var/www/html/Cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/Log \
    && chmod -R 755 /var/www/html/Cache

# Expose port 80
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]
