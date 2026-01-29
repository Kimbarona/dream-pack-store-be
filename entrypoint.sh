#!/bin/sh
set -e

echo "Starting Laravel application..."

echo "Running database migrations..."
php artisan migrate --force

echo "Optimizing Filament..."
php artisan filament:optimize

if [ "$SEED_ADMIN" = "true" ]; then
    echo "Seeding admin user..."
    php artisan db:seed --class=AdminSeeder --force
else
    echo "Skipping admin seeding (SEED_ADMIN not set to 'true')"
fi

echo "Starting PHP server on port ${PORT:-10000}..."
exec php -S 0.0.0.0:${PORT:-10000} -t public