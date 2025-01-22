# Base PHP image with Apache
FROM php:8.2-apache

# Install required packages and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    curl \
    git \
    gnupg \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Node.js and npm
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && apt-get install -y nodejs

# Set document root to Laravel's public folder
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set Apache DocumentRoot to Laravel's public directory
RUN sed -i 's|/var/www/html|/var/www/html/public|' /etc/apache2/sites-available/000-default.conf

# Set the working directory
WORKDIR /var/www/html

# Copy the Laravel app to the container
COPY . /var/www/html

# Install Composer (latest version)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set permissions for storage and bootstrap/cache directories
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Install PHP dependencies using Composer
RUN composer install --no-interaction --optimize-autoloader

# Install Node.js dependencies (e.g., TailwindCSS, Laravel Mix)
RUN npm install

RUN npm install vite@6.0.8

# Expose port 80 (standard HTTP port) for Apache
EXPOSE 80

# Expose an additional port for Artisan serve (default: 8000)
EXPOSE 8001

# Start both Apache and php artisan serve in the background
CMD apache2-foreground & composer run dev --host=0.0.0.0 --port=8000
