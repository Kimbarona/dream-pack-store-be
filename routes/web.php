<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('filament.admin.auth.login');
});

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'environment' => app()->environment(),
        'version' => '1.0.0',
    ]);
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Old admin routes removed - now using Filament

// Banner preview route for Filament
Route::get('/banner/preview/{banner}', function (\App\Models\Banner $banner) {
    return view('banner-preview', compact('banner'));
})->name('banner.preview');

require __DIR__.'/auth.php';

// Apply admin authentication middleware
Route::middleware(['web', 'redirect.if.not.admin'])->group(function () {
    // Admin routes will be handled by Filament
});

// Session test route
Route::get('/_session-test', function () {
    $count = session('test_count', 0) + 1;
    session(['test_count' => $count]);
    
    return response()->json([
        'count' => $count,
        'session_id' => session()->getId(),
        'has_laravel_session' => isset($_COOKIE['laravel_session']),
        'has_cookie_prefix' => request()->cookie ? 'yes' : 'no',
        'cookie_set' => headers_sent(),
        'headers_before' => [],
        'csrf_token' => csrf_token(),
    ]);
})->name('session.test');