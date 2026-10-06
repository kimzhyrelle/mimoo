<?php

use App\Http\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Landing');
})->name('home');

Route::get('/seller', function () {
    return Inertia::render('SellerLanding');
})->name('seller.landing');

Route::middleware(['auth', 'verified', 'approved'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

Route::get('/register', function () {
    return Inertia::render('Register');
})->name('register');

Route::get('/register/seller', function () {
    return Inertia::render('SellerRegistration');
})->name('register.seller');

Route::get('/register/logistics', function () {
    return Inertia::render('LogisticsRegistration');
})->name('register.logistics');

Route::get('/register/pending', function () {
    return Inertia::render('register-pending');
})->name('register.pending');

Route::get('/homepage', function () {
    return Inertia::render('Homepage_GUEST');
})->name('homepage');

Route::get('/cart', function () {
    return Inertia::render('Cart');
})->name('cart');

Route::get('/messages', function () {
    return view('messages');
})->middleware(['auth', 'approved'])->name('messages');

Route::get('/checkout', [App\Http\Controllers\OrderController::class, 'checkout'])
    ->middleware(['auth', 'approved'])
    ->name('checkout');

Route::post('/orders', [App\Http\Controllers\OrderController::class, 'place'])
    ->middleware(['auth', 'approved'])
    ->name('orders.place');

Route::get('/orders/success/{orderNo}', [App\Http\Controllers\OrderController::class, 'success'])
    ->middleware(['auth', 'approved'])
    ->name('order.success');

Route::get('/orders', [App\Http\Controllers\OrderController::class, 'index'])
    ->middleware(['auth', 'approved'])
    ->name('orders.index');

Route::get('/product/{id}', fn (string $id) => Inertia::render('ProductDetail', [
    'productId' => $id,
]))->name('product.show');

require __DIR__.'/settings.php';

// ---------------------------------------------------------------------------
// Admin Panel — secret, session-protected area
// Access via /admin with the master code stored in ADMIN_MASTER_CODE (.env)
// ---------------------------------------------------------------------------
Route::prefix('admin')->name('admin.')->group(function () {
    // Public entry point — show the master code form
    Route::get('/', [AdminController::class, 'index'])->name('login');

    // Code verification — POST only, rate-limited to 10 attempts/minute
    Route::post('/verify', [AdminController::class, 'verify'])->name('verify')->middleware('throttle:10,1');

    // Protected routes — require the session flag set by verify()
    Route::middleware('admin.verified')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/manage-registrations', [AdminController::class, 'manageRegistrations'])->name('manage-registrations');
        Route::post('/registrations/{userId}/approve', [AdminController::class, 'approveRegistration'])->name('registrations.approve');
        Route::post('/registrations/{userId}/disapprove', [AdminController::class, 'disapproveRegistration'])->name('registrations.disapprove');
        Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
    });
});