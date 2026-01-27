<?php

echo "=== Filament Admin Login Verification ===\n";

// Test if the Filament admin panel is working
echo "1. Testing Filament Panel Configuration:\n";

try {
    $panel = \Filament\Facades\Filament::getCurrentPanel();
    if ($panel) {
        echo "   ✅ Panel ID: " . $panel->getId() . "\n";
        echo "   ✅ Panel Path: /" . $panel->getPath() . "\n";
        echo "   ✅ Brand Name: " . $panel->getBrandName() . "\n";
        echo "   ✅ Login Enabled: " . ($panel->hasLogin() ? 'Yes' : 'No') . "\n";
        echo "   ✅ Auth Guard: " . $panel->getAuthGuard() . "\n";
    } else {
        echo "   ❌ Panel not configured\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "   ❌ Panel Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n2. Testing Authentication Configuration:\n";
echo "   APP_URL: " . config('app.url') . "\n";
echo "   Session Driver: " . config('session.driver') . "\n";
echo "   Session Domain: " . (config('session.domain') ?: 'null') . "\n";
echo "   Session Secure: " . (config('session.secure') ? 'true' : 'false') . "\n";
echo "   APP_KEY: " . (config('app.key') ? 'Set' : 'NOT SET') . "\n";

echo "\n3. Testing Admin User:\n";
$admin = \App\Models\Admin::where('email', 'admin@example.com')->first();
if ($admin) {
    echo "   ✅ Admin User: " . $admin->name . " (" . $admin->email . ")\n";
    echo "   ✅ Active: " . ($admin->is_active ? 'Yes' : 'No') . "\n";
} else {
    echo "   ❌ Admin User not found\n";
    exit(1);
}

echo "\n4. Testing Login Attempt:\n";
$success = \Illuminate\Support\Facades\Auth::guard('admin')->attempt([
    'email' => 'admin@example.com', 
    'password' => 'password'
]);

if ($success) {
    echo "   ✅ Authentication successful\n";
    echo "   ✅ Session ID: " . session()->getId() . "\n";
    echo "   ✅ Authenticated User: " . \Illuminate\Support\Facades\Auth::guard('admin')->user()->name . "\n";
} else {
    echo "   ❌ Authentication failed\n";
    exit(1);
}

echo "\n5. Testing CSRF Token:\n";
$token = csrf_token();
echo "   ✅ CSRF Token Generated: " . substr($token, 0, 20) . "...\n";

echo "\n=== VERIFICATION COMPLETE ===\n";
echo "✅ Filament Admin Panel is configured and working\n";
echo "✅ Authentication system is functional\n";
echo "✅ CSRF tokens are generating correctly\n";
echo "✅ Ready for browser testing\n";
echo "\nTest in browser: http://127.0.0.1:8000/admin\n";
echo "Login with: admin@example.com / password\n";