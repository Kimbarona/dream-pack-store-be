# CORS Configuration for Token-Based API + SPA Setup

## 📋 Final config/cors.php

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['http://localhost:3000'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Content-Type',
        'Accept', 
        'Authorization',
        'X-Requested-With',
        'Origin'
    ],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
```

## 📋 Updated bootstrap/app.php (Relevant Section)

```php
// Add CORS middleware to API routes
$middleware->api(prepend: [
    \Fruitcake\Cors\HandleCors::class,
]);

$middleware->alias([
    'web' => \Illuminate\Cookie\Middleware\EncryptCookies::class,
    'redirect.if.not.admin' => \App\Http\Middleware\RedirectIfNotAdmin::class,
]);
```

## 🐳 Docker Commands to Run in Container

```bash
# Clear all Laravel caches (run after config changes)
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
docker compose exec app php artisan route:clear

# Verify configuration is loaded
docker compose exec app php artisan config:get cors
```

## ✅ Browser DevTools Verification Checklist

### 1. OPTIONS Preflight Request Check
```javascript
// In browser console, test OPTIONS request:
fetch('http://localhost:8000/api/auth/register', {
    method: 'OPTIONS',
    headers: {
        'Origin': 'http://localhost:3000',
        'Access-Control-Request-Method': 'POST',
        'Access-Control-Request-Headers': 'Content-Type, Authorization'
    }
})
.then(response => console.log(response.headers))
```

**Expected Response Headers:**
- `Access-Control-Allow-Origin: http://localhost:3000`
- `Access-Control-Allow-Methods: *`
- `Access-Control-Allow-Headers: Content-Type, Accept, Authorization, X-Requested-With, Origin`
- `Access-Control-Max-Age: 0`

### 2. POST Registration Test
```javascript
// Test actual POST request from browser:
fetch('http://localhost:8000/api/auth/register', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Origin': 'http://localhost:3000'
    },
    body: JSON.stringify({
        name: 'Test User',
        email: 'test@example.com',
        password: 'password123',
        password_confirmation: 'password123'
    })
})
.then(response => response.json())
.then(data => console.log(data))
```

**Expected Response Headers:**
- `Access-Control-Allow-Origin: http://localhost:3000` (NOT `*`)
- No `Access-Control-Allow-Credentials` header

### 3. Authenticated Request Test
```javascript
// After login, test with Bearer token:
fetch('http://localhost:8000/api/auth/user', {
    method: 'GET',
    headers: {
        'Authorization': 'Bearer your_token_here',
        'Origin': 'http://localhost:3000'
    }
})
.then(response => response.json())
.then(data => console.log(data))
```

### 4. Expected Error Responses
- **401 Unauthorized** when `Authorization: Bearer token` is missing from protected routes
- **403 Forbidden** when `Origin` is not `http://localhost:3000`
- **422 Validation Error** with proper CORS headers for bad input

## 🔍 Network Tab Inspection

1. **Open DevTools → Network tab** on your Vue app at `http://localhost:3000`
2. **Look for preflight OPTIONS request** before each API call
3. **Verify both OPTIONS and actual request** have:
   - `Access-Control-Allow-Origin: http://localhost:3000`
   - Correct `Access-Control-Allow-Headers`
4. **Ensure no wildcard (*)** in `Access-Control-Allow-Origin`

## 📝 Important Notes

- **No Sanctum CSRF cookie flow** - Pure token-based auth
- **No credentials support** - `supports_credentials: false`
- **Exact origin matching** - Only allows `http://localhost:3000`
- **CORS middleware properly applied** to API routes only

## 🚀 Ready for Local Development

After running the cache clear commands, your CORS should work correctly for Vue SPA at `http://localhost:3000` calling Laravel API at `http://localhost:8000`.