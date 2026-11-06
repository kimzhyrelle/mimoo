<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BuyerOrderController;
use App\Http\Controllers\Api\BuyerPasswordController;
use App\Http\Controllers\Api\BuyerProfileController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

Route::prefix('buyer')->group(function () {
    // ── Auth ─────────────────────────────────────────────────────────────────
    Route::post('/login',            [AuthController::class, 'buyerLogin'])->middleware('throttle:10,1');
    Route::post('/register',         [AuthController::class, 'buyerRegister'])->middleware('throttle:5,1');
    Route::post('/verify-email',     [AuthController::class, 'buyerVerifyEmail'])->middleware('throttle:10,1');
    Route::post('/resend-code',      [AuthController::class, 'buyerResendCode'])->middleware('throttle:3,1');
    Route::post('/forgot-password',  [BuyerPasswordController::class, 'forgotPassword'])->middleware('throttle:5,1');
    Route::post('/reset-password',   [BuyerPasswordController::class, 'resetPassword'])->middleware('throttle:10,1');

    // ── Products (public, no auth required) ──────────────────────────────────
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/products',                                [ProductController::class, 'index']);
        Route::get('/products/{id}',                           [ProductController::class, 'show']);
        Route::get('/products/{id}/image/{index?}',            [ProductController::class, 'image']);
        Route::get('/products/{id}/variant-image/{variantId}', [ProductController::class, 'variantImage']);
    });

    // ── Cart + Orders + Profile (auth required) ───────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {
        // Profile
        Route::get('/profile', [BuyerProfileController::class, 'show']);
        Route::put('/profile', [BuyerProfileController::class, 'update']);

        // Cart
        Route::get('/cart',              [CartController::class, 'index']);
        Route::post('/cart/items',       [CartController::class, 'addItem']);
        Route::patch('/cart/items/{id}', [CartController::class, 'updateItem']);
        Route::delete('/cart/items/{id}',[CartController::class, 'removeItem']);
        Route::delete('/cart',           [CartController::class, 'clear']);

        // Orders
        Route::post('/checkout',           [BuyerOrderController::class, 'checkout'])->middleware('throttle:10,1');
        Route::get('/orders',              [BuyerOrderController::class, 'index']);
        Route::get('/orders/{id}',         [BuyerOrderController::class, 'show']);
        Route::post('/orders/{id}/cancel', [BuyerOrderController::class, 'cancel']);

        Route::post('/logout', [AuthController::class, 'buyerLogout']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me',      [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});