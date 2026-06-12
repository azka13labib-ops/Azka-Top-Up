<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\GameController;
use App\Http\Controllers\Customer\AuthController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\Webhook\MidtransWebhookController;
use App\Http\Controllers\Webhook\DigiflazzWebhookController;

Route::prefix('v1')->group(function () {

    // ─────────────────────────────────────────────
    // Customer Auth Routes (public) — dengan rate limiting
    // ─────────────────────────────────────────────
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:register');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:login');

    // ─────────────────────────────────────────────
    // Admin Auth Routes (public — hanya login)
    // ─────────────────────────────────────────────
    Route::prefix('admin')->group(function () {
        Route::post('/login', [AdminAuthController::class, 'login'])
            ->middleware('throttle:admin-login');
    });

    // ─────────────────────────────────────────────
    // Public Game & Product Routes — dengan rate limiting
    // ─────────────────────────────────────────────
    Route::middleware('throttle:public-api')->group(function () {
        Route::get('/games',                 [GameController::class, 'index']);
        Route::get('/games/{slug}',          [GameController::class, 'show']);
        Route::get('/games/{slug}/products', [GameController::class, 'products']);
    });

    // ─────────────────────────────────────────────
    // Public Order Status (no auth required)
    // ─────────────────────────────────────────────
    Route::get('/orders/{order_code}', [OrderController::class, 'show'])
        ->middleware('throttle:public-api');

    // ─────────────────────────────────────────────
    // Webhook Routes — Lapisan 4: IP Whitelist + Signature validation
    // IMPORTANT: Exclude from CSRF; verified by signature (dan IP jika whitelist aktif).
    // ─────────────────────────────────────────────
    Route::prefix('webhook')->group(function () {
        Route::post('/midtrans', [MidtransWebhookController::class, 'handle'])
            ->middleware('webhook.whitelist:midtrans');

        Route::post('/digiflazz', [DigiflazzWebhookController::class, 'handle'])
            ->middleware('webhook.whitelist:digiflazz');
    });

    // ─────────────────────────────────────────────
    // Authenticated Customer Routes
    // ─────────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout',        [AuthController::class, 'logout']);
        Route::get('/profile',        [ProfileController::class, 'show']);
        Route::get('/profile/orders', [ProfileController::class, 'orders']);

        // Create order: dengan rate limiting khusus
        Route::post('/orders', [OrderController::class, 'store'])
            ->middleware('throttle:create-order');
    });

    // ─────────────────────────────────────────────
    // Admin Protected Routes
    // Lapisan 2: auth:sanctum + EnsureUserIsAdmin ('admin')
    // Memastikan hanya AdminUser yang bisa masuk, bukan customer biasa
    // ─────────────────────────────────────────────
    Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {

        // Admin Auth
        Route::post('/logout', [AdminAuthController::class, 'logout']);
        Route::get('/me',      [AdminAuthController::class, 'me']);

        // Products Management
        Route::get('/products',             [AdminProductController::class, 'index']);
        Route::put('/products/bulk-status', [AdminProductController::class, 'bulkStatus']);
        Route::put('/products/{id}',        [AdminProductController::class, 'update']);
        Route::post('/products/sync',       [AdminProductController::class, 'sync']);

        // Orders Management
        Route::get('/orders',                   [AdminOrderController::class, 'index']);
        Route::get('/orders/{id}',              [AdminOrderController::class, 'show']);
        Route::post('/orders/{id}/retry',       [AdminOrderController::class, 'retry']);
        Route::post('/orders/{id}/flag-refund', [AdminOrderController::class, 'flagRefund']);

        // Balance Check
        Route::get('/balance', [AdminOrderController::class, 'balance']);

        // Site Settings
        Route::get('/settings',         [AdminSettingController::class, 'index']);
        Route::put('/settings',         [AdminSettingController::class, 'update']);
        Route::get('/settings/{key}',   [AdminSettingController::class, 'show']);
        Route::put('/settings/{key}',   [AdminSettingController::class, 'updateSingle']);
    });
});
