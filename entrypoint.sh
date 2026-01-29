#!/bin/sh
set -e

echo "Starting Laravel application..."

echo "==> Ensuring cache directories..."
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/framework/cache/data
chmod -R 775 storage bootstrap/cache || true

echo "==> Running package discovery..."
php artisan package:discover --ansi || true


echo "Running database migrations..."
php artisan migrate --force

echo "Optimizing Filament..."
php artisan filament:optimize || true

if [ "$SEED_ADMIN" = "true" ]; then
    echo "Clearing database for fresh seeding..."
    php artisan migrate:fresh --force
    echo "Seeding database..."
    php artisan db:seed --force
else
    echo "Skipping admin seeding (SEED_ADMIN not set to 'true')"
fi

echo "Starting Laravel artisan serve on port ${PORT:-10000}..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-10000}