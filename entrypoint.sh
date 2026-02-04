#!/bin/sh
set -e

echo "Starting Laravel application..."

echo "==> Ensuring directories..."
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/framework/cache/data storage/app/livewire-tmp storage/app/public bootstrap/cache

# Check Redis availability
redis_available=false
if php -m | grep -i redis >/dev/null 2>&1; then
    echo "==> Redis extension is loaded"
    # Test Redis connection if configured
    if [ -n "$REDIS_HOST" ] || [ -n "$REDIS_URL" ]; then
        if php artisan tinker --execute="try { \Illuminate\Support\Facades\Redis::ping(); echo 'Redis connection successful'; } catch (\Exception \$e) { echo 'Redis connection failed: ' . \$e->getMessage(); exit(1); }" >/dev/null 2>&1; then
            redis_available=true
            echo "==> Redis is available"
        else
            echo "==> Redis is configured but not available"
        fi
    else
        echo "==> Redis extension loaded but not configured"
    fi
else
    echo "==> Redis extension not found"
fi

echo "==> Clearing configuration cache..."
php artisan config:clear

# Only clear cache if it's safe to do so
if [ "$APP_ENV" = "production" ] && [ "$CACHE_STORE" = "redis" ] && [ "$redis_available" = "true" ]; then
    echo "==> Clearing application cache (Redis available)..."
    php artisan cache:clear
elif [ "$APP_ENV" != "production" ]; then
    echo "==> Clearing application cache (development mode)..."
    php artisan cache:clear
else
    echo "==> Skipping cache:clear (Redis unavailable in production)"
fi

echo "==> Running database migrations..."
php artisan migrate --force

# Production optimizations
if [ "$APP_ENV" = "production" ]; then
    echo "==> Optimizing for production..."
    php artisan filament:optimize
    # Removed filament:optimize-clear as it defeats the purpose of optimize
    echo "==> Production optimizations complete"
else
    echo "==> Skipping optimizations (development mode)"
fi

if [ "$SEED_ADMIN" = "true" ]; then
    echo "Clearing database for fresh seeding..."
    php artisan migrate:fresh --force
    echo "Seeding database..."
    php artisan db:seed --force
elif [ "$SEED_ADMIN_ONLY" = "true" ]; then
    echo "Clearing database for admin-only seeding..."
    php artisan migrate:fresh --force
    echo "Seeding admin accounts only..."
    php artisan db:seed --class=AdminOnlySeeder --force
else
    echo "Skipping admin seeding (SEED_ADMIN or SEED_ADMIN_ONLY not set to 'true')"
fi

echo "Starting Laravel artisan serve on port ${PORT:-10000}..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-10000}