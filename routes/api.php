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
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


    // Settings endpoint (from Step 5)
    Route::get('/settings', [SettingsController::class, 'index'])->name('api.settings.index');
    
    // Banners
    Route::get('/banners', [BannerController::class, 'index'])->name('api.banners.index');
    Route::get('/banners/{banner}', [BannerController::class, 'show'])->name('api.banners.show');
    
    // Categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('api.categories.index');
    Route::get('/categories/{slug}', [CategoryController::class, 'show'])->name('api.categories.show');
    
    // Products
    Route::get('/products', [ProductController::class, 'index'])->name('api.products.index');
    Route::get('/products/{slug}', [ProductController::class, 'show'])->name('api.products.show');
    
    // Search
    Route::get('/search', [SearchController::class, 'search'])->name('api.search');
    
    // Orders (requires authentication)
    Route::middleware('auth:sanctum')->group(function () {
        // Create order
        Route::post('/orders', [OrderController::class, 'store'])->name('api.orders.store');
        
        // Get order details
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('api.orders.show');
        
        // Crypto payments
        Route::post('/payments/crypto/invoice/{order}', [CryptoPaymentController::class, 'createInvoice'])->name('api.payments.crypto.create');
        Route::get('/payments/crypto/status/{order}/{invoice}', [CryptoPaymentController::class, 'getInvoiceStatus'])->name('api.payments.crypto.status');
        Route::get('/payments/crypto/currencies', [CryptoPaymentController::class, 'getSupportedCurrencies'])->name('api.payments.crypto.currencies');
        
        // Traditional payments
        Route::post('/payments/traditional/{order}', [TraditionalPaymentController::class, 'createPayment'])->name('api.payments.traditional.create');
        Route::get('/payments/traditional/status/{order}/{payment}', [TraditionalPaymentController::class, 'getPaymentStatus'])->name('api.payments.traditional.status');
        Route::post('/payments/traditional/simulate/{payment}', [TraditionalPaymentController::class, 'simulatePaymentSuccess'])->name('api.payments.traditional.simulate');
        Route::get('/payments/traditional/methods', [TraditionalPaymentController::class, 'getSupportedMethods'])->name('api.payments.traditional.methods');

    
    // Webhooks (no auth - signature verification)
    Route::post('/webhooks/crypto', [CryptoWebhookController::class, 'handle'])->name('api.webhooks.crypto');
    Route::get('/webhooks/crypto/test', [CryptoWebhookController::class, 'testWebhook'])->name('api.webhooks.crypto.test');
    Route::get('/webhooks/crypto/info', [CryptoWebhookController::class, 'getWebhookInfo'])->name('api.webhooks.crypto.info');
});