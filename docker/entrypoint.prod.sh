# Production Dockerfile for Render deployment
FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    zip \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libfreetype6-dev \
    libxml2-dev \
    postgresql-dev \
    oniguruma-dev \
    icu-dev \
    curl \
    git \
    supervisor \
    nginx

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_pgsql \
    pgsql \
    gd \
    zip \
    intl \
    bcmath \
    opcache \
    exif \
    tokenizer \
    xml \
    dom \
    fileinfo \
    mbstring \
    curl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Create nginx config
RUN echo 'server { \
    listen 8080; \
    server_name _; \
    root /var/www/html/public; \
    index index.php; \
    \
    location / { \
        try_files $uri $uri/ /index.php?$query_string; \
    } \
    \
    location ~ \.php$ { \
        try_files $uri =404; \
        fastcgi_pass unix:/run/php/php-fpm.sock; \
        fastcgi_index index.php; \
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; \
        include fastcgi_params; \
    } \
    \
    location ~ /\.ht { \
        deny all; \
    } \
}' > /etc/nginx/conf.d/default.conf

# Create supervisor config
RUN echo '[supervisord] \
nodaemon=true \
\
[program:php-fpm] \
command=php-fpm \
autostart=true \
autorestart=true \
stdout_logfile=/dev/stdout \
stdout_logfile_maxbytes=0 \
stderr_logfile=/dev/stderr \
stderr_logfile_maxbytes=0 \
\
[program:nginx] \
command=nginx -g "daemon off;" \
autostart=true \
autorestart=true \
stdout_logfile=/dev/stdout \
stdout_logfile_maxbytes=0 \
stderr_logfile=/dev/stderr \
stderr_logfile_maxbytes=0' > /etc/supervisor/conf.d/laravel.conf

#!/bin/sh
set -e

# Wait for database
echo "Waiting for database..."
while ! nc -z $DB_HOST $DB_PORT; do
    sleep 1
done
echo "Database is ready!"

# Run Laravel optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:cache-components

# Run migrations
php artisan migrate --force

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Storage link
php artisan storage:link

# Optimize for production
php artisan optimize

# Start supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/laravel.conf

# Expose port
EXPOSE 8080

# Start services
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/laravel.conf"]