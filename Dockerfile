# Use PHP 8.4 CLI image
FROM php:8.4-cli

# Set working directory
WORKDIR /var/www/html

# Set Composer memory limit to unlimited
ENV COMPOSER_MEMORY_LIMIT=-1

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpq-dev \
    libonig-dev \
    libicu-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    mbstring \
    zip \
    intl \
    bcmath

# Configure gd with freetype and jpeg support
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install gd

# Install Redis extension
RUN pecl install redis && \
    docker-php-ext-enable redis

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Fix git "dubious ownership" warning (optional)
RUN git config --global --add safe.directory /var/www/html

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install Composer dependencies without scripts
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts

# Copy the rest of the project
COPY . .

# Ensure Laravel cache paths exist + permissions before artisan runs
RUN mkdir -p storage/framework/{cache,sessions,views} \
    storage/framework/cache/data \
 && chmod -R 775 storage bootstrap/cache

# Ensure storage and bootstrap/cache are writable
RUN chmod -R 775 storage bootstrap/cache

# Create cache directories and clear existing cache
RUN mkdir -p bootstrap/cache storage/framework/{cache,sessions,views} \
    && rm -rf bootstrap/cache/*.php

# Optimize autoloader (package discovery will run in entrypoint)
RUN composer dump-autoload --optimize --no-scripts

# Copy and make executable entrypoint script
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Expose port (Render will set PORT env var)
EXPOSE 10000

# Start the application using entrypoint script
CMD ["/entrypoint.sh"]