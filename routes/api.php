<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CryptoPaymentController;
use App\Http\Controllers\Api\TraditionalPaymentController;
use App\Http\Controllers\Api\CryptoWebhookController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Public endpoints (no authentication required)
Route::prefix('auth')->group(function () {
    // Customer registration and login
    Route::post('/register', [AuthController::class, 'register'])->name('api.auth.register');
    Route::post('/login', [AuthController::class, 'login'])->name('api.auth.login');
});

// Authenticated customer endpoints
Route::middleware('auth:sanctum')->group(function () {
    // Authentication management
    Route::prefix('auth')->group(function () {
        Route::get('/user', [AuthController::class, 'user'])->name('api.auth.user');
        Route::put('/user', [AuthController::class, 'update'])->name('api.auth.update');
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
        Route::post('/logout-all', [AuthController::class, 'logoutAll'])->name('api.auth.logout-all');
        Route::post('/change-password', [AuthController::class, 'changePassword'])->name('api.auth.change-password');
    });
    
    // Orders
    Route::post('/orders', [OrderController::class, 'store'])->name('api.orders.store');
    Route::get('/orders', [OrderController::class, 'index'])->name('api.orders.index');
    Route::get('/orders/user', [OrderController::class, 'showByUser'])->name('api.orders.show-by-user');
    Route::get('/orders/by-user', [OrderController::class, 'getOrdersByUserId'])->name('api.orders.by-user');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('api.orders.show');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancelOrder'])->name('api.orders.cancel');
    Route::post('/orders/{order}/delivered', [OrderController::class, 'markDelivered'])->name('api.orders.delivered');
    
    // Crypto payments
    Route::post('/payments/crypto/invoice/{order}', [CryptoPaymentController::class, 'createInvoice'])->name('api.payments.crypto.create');
    Route::get('/payments/crypto/status/{order}/{invoice}', [CryptoPaymentController::class, 'getInvoiceStatus'])->name('api.payments.crypto.status');
    Route::get('/payments/crypto/currencies', [CryptoPaymentController::class, 'getSupportedCurrencies'])->name('api.payments.crypto.currencies');
    
    // Traditional payments
    Route::post('/payments/traditional/{order}', [TraditionalPaymentController::class, 'createPayment'])->name('api.payments.traditional.create');
    Route::get('/payments/traditional/status/{order}/{payment}', [TraditionalPaymentController::class, 'getPaymentStatus'])->name('api.payments.traditional.status');
    Route::post('/payments/traditional/simulate/{payment}', [TraditionalPaymentController::class, 'simulatePaymentSuccess'])->name('api.payments.traditional.simulate');
    Route::get('/payments/traditional/methods', [TraditionalPaymentController::class, 'getSupportedMethods'])->name('api.payments.traditional.methods');
});

// Public endpoints (no auth required for browsing)
Route::get('/settings', [SettingsController::class, 'index'])->name('api.settings.index');
Route::get('/banners', [BannerController::class, 'index'])->name('api.banners.index');
Route::get('/banners/{banner}', [BannerController::class, 'show'])->name('api.banners.show');
Route::get('/categories', [CategoryController::class, 'index'])->name('api.categories.index');
Route::get('/categories/{slug}', [CategoryController::class, 'show'])->name('api.categories.show');
Route::get('/products', [ProductController::class, 'index'])->name('api.products.index');
Route::get('/products/featured', [ProductController::class, 'featured'])->name('api.products.featured');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('api.products.show');
Route::get('/search', [SearchController::class, 'search'])->name('api.search');

// Webhooks (no auth - signature verification)
Route::post('/webhooks/crypto', [CryptoWebhookController::class, 'handle'])->name('api.webhooks.crypto');
Route::get('/webhooks/crypto/test', [CryptoWebhookController::class, 'testWebhook'])->name('api.webhooks.crypto.test');
Route::get('/webhooks/crypto/info', [CryptoWebhookController::class, 'getWebhookInfo'])->name('api.webhooks.crypto.info');