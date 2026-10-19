<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\ProductController;
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
    Route::get('/seller/dashboard', [\App\Http\Controllers\SellerDashboardController::class, 'index'])->name('seller.dashboard');
    
    // Seller Orders Page
    Route::get('/seller/orders', function () {
        return Inertia::render('seller/SellerOrders');
    })->name('seller.orders');
    
    // Seller Products page
    Route::get('/seller/products', function () {
        $user = Auth::user();
        
        // Fetch products for the authenticated seller
        $products = \App\Models\Product::where('seller_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => (string) $product->id,
                    'name' => $product->name,
                    'description' => $product->description ?? '',
                    'category' => $product->category ?? '',
                    'price' => (float) $product->price,
                    'salePrice' => $product->sale_price ? (float) $product->sale_price : null,
                    'stock' => $product->stock,
                    'sku' => $product->sku,
                    'status' => $product->status === 'active' ? 'published' : 'draft',
                    'images' => $product->images ?? [],
                    'mainIdx' => $product->main_image_index ?? 0,
                    'weight' => $product->weight ? (float) $product->weight : null,
                    'brand' => $product->brand ?? '',
                    'updated' => $product->updated_at->timestamp * 1000, // Convert to milliseconds for JS
                ];
            })->toArray();
        
        return Inertia::render('seller/SellerProducts', [
            'auth' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->email,
                    'account_type' => $user->account_type,
                ],
            ],
            'seller' => [
                'store_name' => $user->business_name ?? 'Your Store',
                'business_name' => $user->business_name,
            ],
            'products' => $products,
        ]);
    })->name('seller.products');
    
    // Seller Settings page
    Route::get('/seller/settings', function () {
        $user = auth()->user();
        return Inertia::render('seller/SellerSettings', [
            'auth' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'account_type' => $user->account_type,
                ],
            ],
            'seller' => [
                'store_name' => $user->business_name ?? $user->name,
                'store_category' => $user->line_of_business ?? 'Health and Beauty',
                'phone' => $user->contact_number ?? '',
                'address_line1' => $user->street_address ?? '',
                'barangay' => $user->barangay ?? '',
                'city' => $user->municipality_city ?? '',
                'province' => $user->province ?? '',
                'postal_code' => '',
            ],
        ]);
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
                // From registration form
                'store_name' => $user->business_name ?? $user->name,
                'store_description' => $user->line_of_business ?? '',
                'store_category' => 'Health and Beauty', // You can add this field to users table later
                'phone' => $user->contact_number ?? '',
                'address_line1' => $user->street_address ?? '',
                'barangay' => $user->barangay ?? '',
                'city' => $user->municipality_city ?? '',
                'province' => $user->province ?? '',
                'postal_code' => '', // Add postal_code field to users table if needed
                'store_logo' => '', // Add store_logo field to users table if needed
                
                // Additional user info
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'middle_initial' => $user->middle_initial,
                'sex' => $user->sex,
                'birthday' => $user->birthday,
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

// Email verification routes (OTP-based)
Route::get('/verify-email', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'showVerification'])
    ->name('otp.show');
Route::post('/verify-email', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'verifyOtp'])
    ->name('otp.verify');
Route::post('/verify-email/resend', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'resendOtp'])
    ->name('otp.resend');

// Privacy Policy page
Route::get('/privacy-policy', function () {
    return Inertia::render('PrivacyPolicy');
})->name('privacy.policy');

// Terms page (assuming you already have this)
Route::get('/terms', function () {
    return Inertia::render('Terms');
})->name('terms');

Route::get('/homepage', function () {
    // Fetch top-selling products from the last 7 days
    $products = \App\Models\Product::with('seller:id,business_name,name')
        ->where('status', 'active')
        ->withCount(['orderItems as total_sold' => function ($query) {
            $query->selectRaw('COALESCE(SUM(quantity), 0)')
                ->whereHas('order', function ($q) {
                    $q->where('status', '!=', 'cancelled')
                        ->where('status', '!=', 'failed')
                        ->where('created_at', '>=', now()->subDays(7));
                });
        }])
        ->orderBy('total_sold', 'desc')
        ->orderBy('created_at', 'desc')
        ->take(20) // Top 20 products
        ->get()
        ->map(function ($product) {
            return [
                'id' => (string) $product->id,
                'name' => $product->name,
                'price' => '₱' . number_format($product->sale_price ?? $product->price, 2),
                'raw_price' => (float) ($product->sale_price ?? $product->price),
                'regular_price' => $product->sale_price ? ('₱' . number_format($product->price, 2)) : null,
                'is_on_sale' => $product->sale_price !== null,
                'stock' => $product->stock,
                'stock_status' => $product->stock_status,
                'is_in_stock' => $product->stock > 0,
                'image' => $product->main_image,
                'seller_name' => $product->seller->business_name ?? $product->seller->name,
                'total_sold' => $product->total_sold ?? 0,
            ];
        })->toArray();
    
    return Inertia::render('Homepage', [
        'products' => $products,
    ]);
})->name('homepage');

Route::get('/messages', function () {
    return view('messages');
})->middleware(['auth', 'approved'])->name('messages');

// Buyer checkout and order routes
Route::middleware(['auth', 'verified', 'approved'])->group(function () {
    Route::get('/cart', function () {
        return Inertia::render('Cart');
    })->name('cart');
    
    Route::get('/checkout', [\App\Http\Controllers\OrderController::class, 'checkout'])->name('checkout');
    Route::get('/orders', [\App\Http\Controllers\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/success', [\App\Http\Controllers\OrderController::class, 'success'])->name('orders.success');
    Route::get('/orders/{id}', [\App\Http\Controllers\OrderController::class, 'show'])->name('orders.show');
});

Route::get('/product/{id}', function (string $id) {
    $product = \App\Models\Product::with('seller:id,business_name,name')
        ->findOrFail($id);
    
    return Inertia::render('ProductDetail', [
        'product' => [
            'id' => (string) $product->id,
            'name' => $product->name,
            'description' => $product->description ?? '',
            'category' => $product->category ?? '',
            'price' => (float) $product->price,
            'sale_price' => $product->sale_price ? (float) $product->sale_price : null,
            'effective_price' => (float) ($product->sale_price ?? $product->price),
            'is_on_sale' => $product->sale_price !== null,
            'stock' => $product->stock,
            'stock_status' => $product->stock_status,
            'is_in_stock' => $product->stock > 0,
            'is_low_stock' => $product->stock > 0 && $product->stock <= 10,
            'images' => $product->images ?? [],
            'main_image_index' => $product->main_image_index ?? 0,
            'weight' => $product->weight ? (float) $product->weight : null,
            'brand' => $product->brand ?? '',
            'sku' => $product->sku,
            'seller_name' => $product->seller->business_name ?? $product->seller->name,
            'seller_id' => $product->seller_id,
            'has_variants' => $product->has_variants ?? false,
            'variant_options' => $product->variant_options ?? null,
            'variants' => $product->variants ?? null,
        ],
    ]);
})->name('product.show');

Route::get('/category/{category}', function (string $category) {
    $products = \App\Models\Product::with('seller:id,business_name,name')
        ->where('category', $category)
        ->where('status', 'active')
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'sale_price' => $product->sale_price ? (float) $product->sale_price : null,
                'effective_price' => (float) ($product->sale_price ?? $product->price),
                'is_on_sale' => $product->sale_price !== null,
                'main_image' => $product->main_image,
                'stock' => $product->stock,
                'category' => $product->category,
                'seller_name' => $product->seller->business_name ?? $product->seller->name,
            ];
        });

    return Inertia::render('CategoryPage', [
        'category' => $category,
        'products' => $products,
    ]);
})->name('category.show');

// Google OAuth routes
Route::get('/auth/google', [SocialiteController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [SocialiteController::class, 'handleGoogleCallback'])->name('auth.google.callback');

// ---------------------------------------------------------------------------
// Product API routes
// ---------------------------------------------------------------------------

// Public product routes (buyers) - upgraded with search, filter, sort, pagination
Route::get('/api/products', [ProductController::class, 'index']); // List active products
Route::get('/api/products/{id}', [ProductController::class, 'show']); // Single product

// Protected buyer routes - Cart & Orders API
Route::middleware(['auth', 'verified', 'approved'])->group(function () {
    // Cart
    Route::get('/api/cart', [\App\Http\Controllers\CartController::class, 'index']);
    Route::get('/api/cart/count', [\App\Http\Controllers\CartController::class, 'getCount']);
    Route::post('/api/cart/items', [\App\Http\Controllers\CartController::class, 'addItem']);
    Route::patch('/api/cart/items/{id}', [\App\Http\Controllers\CartController::class, 'updateItem']);
    Route::delete('/api/cart/items/{id}', [\App\Http\Controllers\CartController::class, 'removeItem']);
    
    // Orders
    Route::post('/api/orders', [\App\Http\Controllers\OrderController::class, 'store']);
    Route::post('/api/orders/{id}/cancel', [\App\Http\Controllers\OrderController::class, 'cancel']);
    
    // Orders data refresh endpoint
    Route::get('/orders/data', [\App\Http\Controllers\OrderController::class, 'getData']);
});

// Protected seller product routes
Route::middleware(['auth', 'verified', 'approved'])->group(function () {
    // Analytics
    Route::get('/api/seller/analytics', [\App\Http\Controllers\SellerAnalyticsController::class, 'index']);
    
    // Products CRUD
    Route::get('/api/seller/products', [ProductController::class, 'sellerIndex']); // Seller's own products (upgraded with filters)
    Route::post('/api/products', [ProductController::class, 'store']); // Create product
    Route::patch('/api/products/{id}', [ProductController::class, 'update']); // Update product (all fields)
    Route::patch('/api/products/{id}/stock', [ProductController::class, 'updateStock']); // Update stock only
    Route::delete('/api/products/{id}', [ProductController::class, 'destroy']); // Soft delete product
    
    // Stock logs
    Route::get('/api/products/{id}/stock-logs', [ProductController::class, 'getStockLogs']);
    
    // Seller Orders
    Route::get('/api/seller/orders', [\App\Http\Controllers\SellerOrderController::class, 'index']);
    Route::get('/api/seller/orders/recent', [\App\Http\Controllers\SellerOrderController::class, 'recent']);
    Route::patch('/api/seller/orders/{id}/status', [\App\Http\Controllers\SellerOrderController::class, 'updateStatus']);
    
    // Real-time seller dashboard stream (SSE)
    Route::get('/api/seller/dashboard/stream', [\App\Http\Controllers\SellerDashboardStreamController::class, 'stream']);
});

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
        
        // Seller approval routes
        Route::get('/seller-approval', [AdminController::class, 'sellerApproval'])->name('seller-approval');
        Route::post('/seller-approval/{userId}/approve', [AdminController::class, 'approveSellerRegistration'])->name('seller-approval.approve');
        Route::post('/seller-approval/{userId}/disapprove', [AdminController::class, 'disapproveSellerRegistration'])->name('seller-approval.disapprove');
        
        Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
    });
});