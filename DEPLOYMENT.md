# Production Deployment Guide for Render

## 🚀 Overview
This document covers all the fixes and configurations needed for successful deployment to Render.

## ✅ Fixes Implemented

### 1. HTTPS Detection Behind Render Proxy
- **TrustProxies middleware** (`app/Http/Middleware/TrustProxies.php`):
  - Set `protected $proxies = '*'` to trust all proxies
  - Included all forwarded headers including `X_FORWARDED_PROTO`
- **AppServiceProvider** (`app/Providers/AppServiceProvider.php`):
  - Added `URL::forceScheme('https')` for production environment

### 2. Environment Variables for Production
- **Production template** (`.env.production.example`):
  - `APP_ENV=production`
  - `APP_DEBUG=false`
  - `APP_URL=https://dream-pack-store-be-1.onrender.com`
  - `ASSET_URL=https://dream-pack-store-be-1.onrender.com`
  - `SESSION_SECURE_COOKIE=true`
  - `SESSION_ENCRYPT=true`

### 3. Asset Loading & Vite Configuration
- **Updated Vite config** (`vite.config.js`):
  - Enhanced build configuration for production
  - Proper output directory and manifest handling
  - Server configuration for development
- **Frontend assets** properly included in Blade templates via `@vite(['resources/css/app.css', 'resources/js/app.js'])`

### 4. Deployment Scripts & Cache Optimization
- **Deployment script** (`scripts/deploy.sh`):
  - Composer dependencies with `--optimize-autoloader`
  - Node dependencies with `npm ci`
  - Frontend build with `npm run build`
  - Complete cache clearing and rebuilding
  - Filament optimization
  - Database migrations and storage linking
- **Startup script** (`scripts/start.sh`):
  - Proper server startup with storage linking
  - Permission fixes for production

## 📋 Render Configuration

### Environment Variables (Set in Render Dashboard)
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://dream-pack-store-be-1.onrender.com
ASSET_URL=https://dream-pack-store-be-1.onrender.com
SESSION_SECURE_COOKIE=true
SESSION_ENCRYPT=true
# Database variables will be auto-populated by Render
DB_CONNECTION=pgsql
DB_HOST=${DB_HOST}
DB_PORT=${DB_PORT}
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}
```

### Build & Start Commands
- **Build Command**: `bash scripts/deploy.sh`
- **Start Command**: `bash scripts/start.sh`

### Service Configuration
```yaml
services:
  - type: web
    name: dream-pack-store
    env: php
    buildCommand: bash scripts/deploy.sh
    startCommand: bash scripts/start.sh
    healthCheckPath: /health
```

## 🔧 Local Development Setup

### Quick Start
```bash
# Set up local development environment
bash scripts/dev-setup.sh

# Start development servers
npm run dev & php artisan serve
```

### Development Environment Variables
```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
ASSET_URL=http://127.0.0.1:8000
```

## ✅ Verification Checklist

After deployment, verify:

### 1. Admin Login Page
- URL: `https://dream-pack-store-be-1.onrender.com/admin/login`
- ✅ Filament UI loads with full styling
- ✅ No missing assets in Network tab
- ✅ All CSS/JS files load via HTTPS

### 2. HTTPS & Security
- ✅ No mixed-content warnings in browser console
- ✅ All form actions use HTTPS
- ✅ Logout form doesn't show "not secure" warning
- ✅ Session cookies are secure (secure flag set)

### 3. Dashboard Functionality
- ✅ Dashboard loads without errors
- ✅ No `ComponentNotFoundException` for widgets
- ✅ All Filament resources work correctly

### 4. Health Check
- ✅ `https://dream-pack-store-be-1.onrender.com/health` returns:
  ```json
  {
    "status": "ok",
    "timestamp": "2026-01-30T...",
    "environment": "production",
    "version": "1.0.0"
  }
  ```

## 🚨 Common Issues & Solutions

### Issue: CSS/JS not loading
- **Cause**: Incorrect `APP_URL` or `ASSET_URL`
- **Fix**: Ensure both are set to HTTPS URL in Render environment

### Issue: "Not secure" form warning
- **Cause**: Missing HTTPS force scheme or proxy headers
- **Fix**: Verify TrustProxies middleware and AppServiceProvider changes

### Issue: Filament component errors
- **Cause**: Stale caches or missing Filament optimization
- **Fix**: Run `php artisan filament:optimize-clear && php artisan filament:optimize`

### Issue: Database connection errors
- **Cause**: Database environment variables not properly set
- **Fix**: Verify Render database environment variables are correctly mapped

## 🔄 Deployment Process

1. Push code to repository
2. Render automatically triggers build
3. Build script runs all optimization steps
4. Application starts with HTTPS properly configured
5. Health check passes
6. Deployment complete

## 📝 Notes

- The Dashboard is temporarily plain (widgets disabled) to avoid component errors
- Categories/Sub-Category fields have been added to Products form
- All configurations preserve local development compatibility
- Scripts include proper error handling and progress feedback