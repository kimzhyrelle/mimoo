<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\AreaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourierController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DispatchController;
use App\Http\Controllers\Api\ParcelController;
use App\Http\Controllers\Api\RiderController;
use App\Http\Controllers\Api\ShipmentController as PublicShipmentController;
use App\Http\Controllers\Api\ShippingController;
use App\Http\Controllers\DeliveryAssignmentController;
use App\Http\Controllers\ShipmentController;
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

// Checkout quote (buyer/seller site) and cross-role shipment tracking.
Route::get('/shipping/quote', [ShippingController::class, 'quote']);
Route::post('/shipping/quote', [ShippingController::class, 'quote']);
Route::get('/shipments/{trackingNo}/tracking', [PublicShipmentController::class, 'tracking']);
Route::post('/shipments/{trackingNo}/otp', [PublicShipmentController::class, 'verifyOtp']);

// ── Protected routes (token required) ────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Parcels
    Route::get('/parcels', [ParcelController::class, 'index']);
    Route::get('/parcels/{parcel}', [ParcelController::class, 'show']);
    Route::post('/parcels/{parcel}/scan', [ParcelController::class, 'scan']);
    Route::post('/parcels/{parcel}/determine-area', [ParcelController::class, 'determineArea']);
    Route::post('/parcels/{parcel}/assign-rider', [ParcelController::class, 'assignRider']);
    Route::post('/parcels/{parcel}/dispatch', [ParcelController::class, 'dispatch']);
    Route::post('/parcels/{parcel}/advance', [ParcelController::class, 'advance']);
    Route::post('/parcels/{parcel}/scan-undelivered', [ParcelController::class, 'scanUndelivered']);

    // Shipments (state machine, undo, archive)
    Route::post('/shipments/status', [ShipmentController::class, 'updateStatus']);
    Route::post('/shipments/{parcel}/status', [ShipmentController::class, 'updateStatus']);
    Route::post('/shipments/undo', [ShipmentController::class, 'undo']);
    Route::post('/shipments/{parcel}/archive', [ShipmentController::class, 'archive']);
    Route::post('/shipments/archive', [ShipmentController::class, 'archive']);
    Route::post('/shipments/{parcel}/restore', [ShipmentController::class, 'restore']);
    Route::post('/shipments/restore', [ShipmentController::class, 'restore']);

    // Areas (barangays)
    Route::get('/areas', [AreaController::class, 'index']);

    // Riders
    Route::get('/riders', [RiderController::class, 'index']);
    Route::get('/riders/load', [RiderController::class, 'load']);
    Route::get('/riders/{rider}', [RiderController::class, 'show']);
    Route::patch('/riders/{rider}/toggle-active', [RiderController::class, 'toggleActive']);
    Route::patch('/riders/{rider}/barangay', [RiderController::class, 'setBarangay']);
    Route::post('/assignments/bulk', [DeliveryAssignmentController::class, 'bulkAssign']);

    // Courier app
    Route::get('/courier/{rider}', [CourierController::class, 'show']);
    Route::post('/courier/{rider}/claim', [CourierController::class, 'claim']);
    Route::post('/courier/{rider}/advance', [CourierController::class, 'advance']);

    // Rider applications
    Route::get('/rider-applications', [RiderController::class, 'applications']);
    Route::get('/rider-applications/{application}', [RiderController::class, 'showApplication']);
    Route::post('/rider-applications/{application}/approve', [RiderController::class, 'approveApplication']);
    Route::post('/rider-applications/{application}/disapprove', [RiderController::class, 'disapproveApplication']);

    // Dispatch board
    Route::get('/dispatch', [DispatchController::class, 'board']);
    Route::post('/dispatch/area/{area}', [DispatchController::class, 'dispatchArea']);
    Route::post('/dispatch/all', [DispatchController::class, 'dispatchAll']);

    // Activity feed
    Route::get('/activities', [ActivityController::class, 'index']);
    Route::post('/activities/read-all', [ActivityController::class, 'markAllRead']);
    Route::post('/activities/{activity}/read', [ActivityController::class, 'markRead']);

    // Dispatch eligibility and assignment
    Route::get('/dispatch/eligible-riders', [DispatchController::class, 'eligibleRiders']);
    Route::post('/dispatch/{parcel}/auto-assign', [DispatchController::class, 'autoAssign']);
    Route::post('/dispatch/{parcel}/assign', [DispatchController::class, 'assign']);
    Route::post('/dispatch/{parcel}/reassign', [DispatchController::class, 'reassign']);

    // Shipment events, notifications and audit trail
    Route::get('/shipments/{parcel}/events', [PublicShipmentController::class, 'events']);
    Route::get('/notifications', [PublicShipmentController::class, 'notifications']);
    Route::get('/audit-logs', [PublicShipmentController::class, 'auditLogs']);
});
