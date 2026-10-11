<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\SocialiteController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Landing');
})->name('home');

Route::get('/seller', function () {
    return Inertia::render('seller/SellerLanding');
})->name('seller.landing');

// TEST ROUTE - NO AUTH REQUIRED (REMOVE LATER!)
Route::get('/test-seller-dashboard', function () {
    return Inertia::render('seller/SellerDashboard');
});

Route::get('/logistics', function () {
    return Inertia::render('logistics/LogisticsLanding');
})->name('logistics.landing');

Route::middleware(['auth', 'verified', 'approved'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
    
    // Debug route - check your account type
    Route::get('/debug/me', function () {
        $user = auth()->user();
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'account_type' => $user->account_type,
            'status' => $user->status,
        ]);
    });
    
    // Seller Dashboard - for approved sellers only
    Route::get('/seller/dashboard', function () {
        return Inertia::render('seller/SellerDashboard');
    })->name('seller.dashboard');
    
    // Seller Products page
    Route::get('/seller/products', function () {
        return Inertia::render('seller/SellerProducts');
    })->name('seller.products');
    
    // Seller Settings page
    Route::get('/seller/settings', function () {
        return Inertia::render('seller/SellerSettings');
    })->name('seller.settings');
    
    // Seller Account Management page
    Route::get('/seller/account', function () {
        $user = auth()->user();
        return Inertia::render('seller/SellerAccount', [
            'auth' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'account_type' => $user->account_type,
                ],
            ],
            'seller' => [
                'store_name' => $user->name, // For now, use user name as store name
                'store_description' => '',
                'store_category' => 'Health and Beauty',
                'phone' => '',
                'address_line1' => '',
                'barangay' => '',
                'city' => '',
                'province' => '',
                'postal_code' => '',
                'store_logo' => '',
            ],
        ]);
    })->name('seller.account');
});

Route::get('/register', function () {
    return Inertia::render('Register');
})->name('register');

Route::get('/register/seller', function () {
    return Inertia::render('seller/SellerRegistration');
})->name('register.seller');

Route::get('/register/logistics', function () {
    return Inertia::render('logistics/LogisticsRegistration');
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

// Google OAuth routes
Route::get('/auth/google', [SocialiteController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [SocialiteController::class, 'handleGoogleCallback'])->name('auth.google.callback');

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