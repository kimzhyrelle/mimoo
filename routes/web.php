<?php

use App\Http\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Landing');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

Route::get('/register', function () {
    return Inertia::render('Register');
});

Route::get('/register/pending', function () {
    return Inertia::render('register-pending');
})->name('register.pending');

Route::get('/homepage', function () {
    return Inertia::render('Homepage_GUEST');
})->name('homepage');

Route::get('/cart', function () {
    return Inertia::render('Cart');
})->name('cart');

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
        Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
    });
});