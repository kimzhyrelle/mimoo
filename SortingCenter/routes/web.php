<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryAssignmentController;
use App\Http\Controllers\DeliveryMonitoringController;
use App\Http\Controllers\IncomingParcelsController;
use App\Http\Controllers\RiderManagementController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SortParcelsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Sorting Center routes — merge these into your project's routes/web.php
|--------------------------------------------------------------------------
| No login: every page below is public. If you bring auth back later,
| re-wrap this block in Route::middleware('auth')->group(...) and restore
| SortingLoginController + the guest/login routes (still in Stage 1–2 of
| this project's history if you need to resurrect them).
*/

Route::get('/', fn () => redirect()->route('dashboard'));

/*
|--------------------------------------------------------------------------
| UI-only preview — fake data instead of the database. Delete this whole
| group once you're building against real data; the routes below already
| work without login, so at that point /preview/* is just a duplicate of
| the real pages with made-up numbers.
|--------------------------------------------------------------------------
*/
Route::prefix('preview')->name('preview.')->controller(\App\Http\Controllers\DevPreviewController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/dashboard', 'dashboard')->name('dashboard');
    Route::get('/parcels/incoming', 'incomingParcels')->name('parcels.incoming');
    Route::get('/parcels/sort', 'sortParcels')->name('parcels.sort');
    Route::get('/delivery/monitoring', 'deliveryMonitoring')->name('delivery.monitoring');
    Route::get('/riders', 'riderManagement')->name('riders.index');
    Route::get('/account', 'account')->name('account.index');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// --- Incoming Parcels ---
Route::get('/parcels/incoming', [IncomingParcelsController::class, 'index'])->name('parcels.incoming');
Route::post('/parcels/incoming/{parcel}/scan', [IncomingParcelsController::class, 'scan'])->name('parcels.incoming.scan');
Route::post('/parcels/incoming/scan-all', [IncomingParcelsController::class, 'scanAll'])->name('parcels.incoming.scan-all');

// --- Sort Parcels ---
Route::get('/parcels/sort', [SortParcelsController::class, 'index'])->name('parcels.sort');
Route::post('/parcels/sort/{parcel}/determine-area', [SortParcelsController::class, 'determineArea'])->name('parcels.sort.determine-area');
Route::post('/parcels/sort/{parcel}/assign-rider', [SortParcelsController::class, 'assignRider'])->name('parcels.sort.assign-rider');
Route::post('/parcels/sort/{parcel}/carrier', [SortParcelsController::class, 'setCarrier'])->name('parcels.sort.carrier');

// --- Delivery Monitoring ---
Route::get('/delivery/monitoring', [DeliveryMonitoringController::class, 'index'])->name('delivery.monitoring');
Route::post('/delivery/monitoring/{parcel}/advance', [DeliveryMonitoringController::class, 'advance'])->name('delivery.monitoring.advance');
Route::post('/delivery/monitoring/{parcel}/scan-undelivered', [DeliveryMonitoringController::class, 'scanUndelivered'])->name('delivery.monitoring.scan-undelivered');

// --- Rider Management ---
Route::get('/riders', [RiderManagementController::class, 'index'])->name('riders.index');
Route::post('/riders/applications/{application}/approve', [RiderManagementController::class, 'approve'])->name('riders.applications.approve');
Route::post('/riders/applications/{application}/disapprove', [RiderManagementController::class, 'disapprove'])->name('riders.applications.disapprove');
Route::post('/riders/{rider}/barangay', [RiderManagementController::class, 'setBarangay'])->name('riders.barangay');
Route::post('/riders/{rider}/toggle-active', [RiderManagementController::class, 'toggleActive'])->name('riders.toggle-active');

// --- Notifications ---
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
Route::post('/notifications/{activity}/read', [NotificationController::class, 'markRead'])->name('notifications.mark-read');

// --- Account ---
Route::get('/account', [AccountController::class, 'edit'])->name('account.index');
Route::post('/account', [AccountController::class, 'update'])->name('account.update');

// --- Still stubbed — next build stages ---
// --- Delivery Assignment ---
Route::get('/delivery/assignment', [DeliveryAssignmentController::class, 'index'])->name('delivery.assignment');
Route::post('/delivery/assignment/dispatch-all', [DeliveryAssignmentController::class, 'dispatchAll'])->name('delivery.assignment.dispatch-all');
Route::post('/delivery/assignment/{area}/dispatch', [DeliveryAssignmentController::class, 'dispatchArea'])->name('delivery.assignment.dispatch-area');
Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
