#!/bin/bash

# Local Development Setup Script
# This script helps set up the local development environment

echo "🏠 Setting up local development environment..."

# Install Composer dependencies
echo "📦 Installing Composer dependencies..."
composer install

# Install Node dependencies
echo "📦 Installing Node dependencies..."
npm install

# Copy environment file if not exists
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate

# Link storage
echo "🔗 Linking storage directory..."
php artisan storage:link

# Clear caches
echo "🧹 Clearing caches..."
php artisan optimize:clear

echo "✅ Local development setup completed!"
echo ""
echo "🚀 To start development:"
echo "   npm run dev     # Start Vite development server"
echo "   php artisan serve # Start Laravel development server"
echo ""
echo "   Or run both at once:"
echo "   npm run dev & php artisan serve"