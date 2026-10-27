<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\SortingLoginController;
use App\Http\Controllers\BranchChecklistController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CodController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryAssignmentController;
use App\Http\Controllers\DeliveryMonitoringController;
use App\Http\Controllers\DevPreviewController;
use App\Http\Controllers\DispatchController;
use App\Http\Controllers\IncomingParcelsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RiderManagementController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\SortParcelsController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\ZoneRateController;
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

// Login is off by default (see SIGN_IN_ENABLED). It defaults on only in production.
if (config('sorting.sign_in_enabled')) {
    Route::get('/login', [SortingLoginController::class, 'show'])->name('login');
    Route::post('/login', [SortingLoginController::class, 'login'])->name('login.store');
    Route::post('/logout', [SortingLoginController::class, 'logout'])->name('logout');
}

/*
|--------------------------------------------------------------------------
| UI-only preview — fake data instead of the database. Delete this whole
| group once you're building against real data; the routes below already
| work without login, so at that point /preview/* is just a duplicate of
| the real pages with made-up numbers.
|--------------------------------------------------------------------------
*/
Route::prefix('preview')->name('preview.')->controller(DevPreviewController::class)->group(function () {
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

// --- Shipments State Machine, Undo & Archive ---
Route::post('/shipments/status', [ShipmentController::class, 'updateStatus'])->name('shipments.status');
Route::post('/shipments/{parcel}/status', [ShipmentController::class, 'updateStatus']);
Route::post('/shipments/undo/{token?}', [ShipmentController::class, 'undo'])->name('shipments.undo');
Route::post('/shipments/archive', [ShipmentController::class, 'archive'])->name('shipments.archive');
Route::post('/shipments/{parcel}/archive', [ShipmentController::class, 'archive'])->name('shipments.archive.parcel');
Route::post('/shipments/{parcel}/override', [ShipmentController::class, 'override'])->name('shipments.override');
Route::post('/shipments/{parcel}/failure', [ShipmentController::class, 'failDelivery'])->name('shipments.failure');
Route::post('/shipments/{parcel}/lost', [ShipmentController::class, 'lost'])->name('shipments.lost');
Route::post('/shipments/{parcel}/damaged', [ShipmentController::class, 'damaged'])->name('shipments.damaged');
Route::post('/shipments/{parcel}/seller-receipt', [ShipmentController::class, 'sellerReceipt'])->name('shipments.seller-receipt');
Route::post('/parcels/sort/bulk-archive', [ShipmentController::class, 'bulkArchive'])->name('parcels.sort.bulk-archive');
Route::post('/shipments/restore', [ShipmentController::class, 'restore'])->name('shipments.restore');
Route::post('/shipments/{parcel}/restore', [ShipmentController::class, 'restore'])->name('shipments.restore.parcel');

// --- Rider Management ---
Route::get('/riders', [RiderManagementController::class, 'index'])->name('riders.index');
Route::get('/riders/load', [RiderManagementController::class, 'load'])->name('riders.load');
Route::post('/riders/applications/{application}/verification', [RiderManagementController::class, 'updateVerification'])->name('riders.applications.verification');
Route::post('/riders/applications/{application}/otp/send', [RiderManagementController::class, 'sendOtp'])->name('riders.applications.otp.send');
Route::post('/riders/applications/{application}/otp/verify', [RiderManagementController::class, 'verifyOtp'])->name('riders.applications.otp.verify');
Route::post('/riders/applications/{application}/approve', [RiderManagementController::class, 'approve'])->middleware('rider.approval')->name('riders.applications.approve');
Route::post('/riders/applications/{application}/disapprove', [RiderManagementController::class, 'disapprove'])->middleware('rider.approval')->name('riders.applications.disapprove');
Route::post('/riders/{rider}/barangay', [RiderManagementController::class, 'setBarangay'])->name('riders.barangay');
Route::post('/riders/{rider}/toggle-active', [RiderManagementController::class, 'toggleActive'])->name('riders.toggle-active');
Route::post('/riders/{rider}/phone', [RiderManagementController::class, 'changePhone'])->name('riders.phone');
Route::post('/riders/{rider}/otp/send', [RiderManagementController::class, 'sendRiderOtp'])->name('riders.otp.send');
Route::post('/riders/{rider}/otp/verify', [RiderManagementController::class, 'verifyRiderOtp'])->name('riders.otp.verify');
Route::post('/riders/{rider}/suspend', [RiderManagementController::class, 'suspend'])->name('riders.suspend');
Route::post('/riders/{rider}/reactivate', [RiderManagementController::class, 'reactivate'])->name('riders.reactivate');
Route::post('/riders/{rider}/approve', [RiderManagementController::class, 'approveRider'])->name('riders.approve');

// --- Branches & Checklists ---
Route::get('/branches', [BranchChecklistController::class, 'index'])->name('branches.index');
Route::post('/branches/checklist', [BranchChecklistController::class, 'submit'])->name('branches.checklist.submit');

// --- Notifications ---
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
Route::post('/notifications/{activity}/read', [NotificationController::class, 'markRead'])->name('notifications.mark-read');

// --- Account ---
Route::get('/account', [AccountController::class, 'edit'])->name('account.index');
Route::post('/account', [AccountController::class, 'update'])->name('account.update');

// --- Delivery Assignment ---
Route::get('/delivery/assignment', [DeliveryAssignmentController::class, 'index'])->name('delivery.assignment');
Route::get('/delivery/assignment/dispatch-list', [DeliveryAssignmentController::class, 'dispatchList'])->name('delivery.assignment.dispatch-list');
Route::post('/delivery/assignment/dispatch-list', [DeliveryAssignmentController::class, 'generateDispatchList'])->name('delivery.assignment.dispatch-list.generate');
Route::get('/delivery/assignment/dispatch-list/{manifest}', [DeliveryAssignmentController::class, 'showDispatchList'])->name('delivery.assignment.dispatch-list.show');
Route::get('/delivery/assignment/weigh-ins', [DeliveryAssignmentController::class, 'weighIns'])->name('delivery.assignment.weigh-ins');
Route::post('/delivery/assignment/parcels/{parcel}/weigh-ins', [DeliveryAssignmentController::class, 'recordWeighIn'])->name('delivery.assignment.weigh-ins.store');
Route::post('/delivery/assignment/dispatch-all', [DeliveryAssignmentController::class, 'dispatchAll'])->name('delivery.assignment.dispatch-all');
Route::post('/delivery/assignment/{area}/dispatch', [DeliveryAssignmentController::class, 'dispatchArea'])->name('delivery.assignment.dispatch-area');
Route::post('/assignments/bulk', [DeliveryAssignmentController::class, 'bulkAssign'])->name('assignments.bulk');

// --- Dispatch / Sort & Assign ---
Route::post('/dispatch/parcels/{parcel}/assign', [DispatchController::class, 'assign'])->name('dispatch.assign');
Route::post('/dispatch/parcels/{parcel}/auto-assign', [DispatchController::class, 'autoAssign'])->name('dispatch.auto-assign');
Route::post('/dispatch/parcels/{parcel}/reassign', [DispatchController::class, 'reassign'])->name('dispatch.reassign');
Route::post('/dispatch/bulk', [DispatchController::class, 'bulk'])->name('dispatch.bulk');

// --- Zones & Rates ---
Route::get('/zones', [ZoneRateController::class, 'index'])->name('zones.index');
Route::post('/zones', [ZoneRateController::class, 'storeZone'])->name('zones.store');
Route::post('/zones/{zone}', [ZoneRateController::class, 'updateZone'])->name('zones.update');
Route::post('/zones/{zone}/rates', [ZoneRateController::class, 'storeRate'])->name('rates.store');
Route::post('/zones/{zone}/rates/{rate}', [ZoneRateController::class, 'updateRate'])->name('rates.update');

// --- COD & Reports ---
Route::get('/cod', [CodController::class, 'index'])->name('cod.index');
Route::post('/cod/remittances', [CodController::class, 'store'])->name('cod.remittances.store');
Route::post('/cod/remittances/{remittance}/confirm', [CodController::class, 'confirm'])->name('cod.remittances.confirm');

// --- Support / Issue Reports ---
Route::get('/support', [SupportController::class, 'index'])->name('support.index');
Route::get('/support/new', [SupportController::class, 'create'])->name('support.create');
Route::post('/support', [SupportController::class, 'store'])->name('support.store');
Route::get('/support/{report}', [SupportController::class, 'show'])->name('support.show');
Route::post('/support/{report}/replies', [SupportController::class, 'reply'])->name('support.replies.store');
Route::post('/support/{report}/status', [SupportController::class, 'updateStatus'])->name('support.status');

Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
Route::get('/reports/show', [ReportsController::class, 'show'])->name('reports.show');
Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
Route::post('/chat/{conversation}/messages', [ChatController::class, 'store'])->name('chat.messages.store');
Route::post('/chat/{conversation}/read', [ChatController::class, 'markRead'])->name('chat.read');

// --- Global Search ---
Route::get('/search', [SearchController::class, 'index'])->name('search');

Route::get('/help', fn () => view('sorting.help'))->name('help.index');
