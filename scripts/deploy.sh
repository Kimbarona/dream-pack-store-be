#!/bin/bash

# Production Deployment Script for Render
# This script handles the complete deployment process

set -e

echo "🚀 Starting production deployment..."

# Step 1: Install Composer dependencies
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Step 2: Install Node dependencies
echo "📦 Installing Node dependencies..."
npm ci --no-optional

# Step 3: Build frontend assets
echo "🔨 Building frontend assets..."
npm run build

# Step 4: Clear all caches first
echo "🧹 Clearing all caches..."
php artisan optimize:clear
php artisan filament:optimize-clear

# Step 5: Build caches for production
echo "🏗️ Building caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Step 6: Optimize Filament
echo "⚡ Optimizing Filament..."
php artisan filament:optimize

# Step 7: Run database migrations (safe for production)
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Step 8: Link storage directory
echo "🔗 Linking storage directory..."
php artisan storage:link

# Step 9: Clear any leftover caches
echo "🧹 Final cache cleanup..."
php artisan cache:clear

echo "✅ Deployment completed successfully!"