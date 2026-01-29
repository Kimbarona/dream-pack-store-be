#!/bin/bash

# Production Startup Script for Render
# This script handles the server startup for production

# Link storage if not exists
if [ ! -L public/storage ]; then
    php artisan storage:link
fi

# Ensure the public directory is writable for logs
mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views

# Set proper permissions
chmod -R 755 storage bootstrap/cache

# Start PHP built-in server
php -S 0.0.0.0:${PORT} -t public public/index.php