<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\AreaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DispatchController;
use App\Http\Controllers\Api\ParcelController;
use App\Http\Controllers\Api\RiderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mimoo Sorting Center — Logistics API
|--------------------------------------------------------------------------
| Base URL: /api
| Auth: Bearer token (Laravel Sanctum)
|--------------------------------------------------------------------------
*/

// ── Public routes (no token required) ────────────────────────────────────
Route::post('/auth/login', [AuthController::class, 'login']);

// ── Protected routes (token required) ────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Parcels
    Route::get('/parcels',                              [ParcelController::class, 'index']);
    Route::get('/parcels/{parcel}',                     [ParcelController::class, 'show']);
    Route::post('/parcels/{parcel}/scan',               [ParcelController::class, 'scan']);
    Route::post('/parcels/{parcel}/determine-area',     [ParcelController::class, 'determineArea']);
    Route::post('/parcels/{parcel}/assign-rider',       [ParcelController::class, 'assignRider']);
    Route::post('/parcels/{parcel}/dispatch',           [ParcelController::class, 'dispatch']);
    Route::post('/parcels/{parcel}/advance',            [ParcelController::class, 'advance']);
    Route::post('/parcels/{parcel}/scan-undelivered',   [ParcelController::class, 'scanUndelivered']);

    // Areas (barangays)
    Route::get('/areas', [AreaController::class, 'index']);

    // Riders
    Route::get('/riders',                                           [RiderController::class, 'index']);
    Route::get('/riders/{rider}',                                   [RiderController::class, 'show']);
    Route::patch('/riders/{rider}/toggle-active',                   [RiderController::class, 'toggleActive']);
    Route::patch('/riders/{rider}/barangay',                        [RiderController::class, 'setBarangay']);

    // Rider applications
    Route::get('/rider-applications',                               [RiderController::class, 'applications']);
    Route::post('/rider-applications/{application}/approve',        [RiderController::class, 'approveApplication']);
    Route::post('/rider-applications/{application}/disapprove',     [RiderController::class, 'disapproveApplication']);

    // Dispatch board
    Route::get('/dispatch',              [DispatchController::class, 'board']);
    Route::post('/dispatch/area/{area}', [DispatchController::class, 'dispatchArea']);
    Route::post('/dispatch/all',         [DispatchController::class, 'dispatchAll']);

    // Activity feed
    Route::get('/activities',                    [ActivityController::class, 'index']);
    Route::post('/activities/read-all',          [ActivityController::class, 'markAllRead']);
    Route::post('/activities/{activity}/read',   [ActivityController::class, 'markRead']);
});
