<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Auth;
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
    // Generic dashboard - redirect based on account type
    Route::get('dashboard', function () {
        $user = auth()->user();
        
        return match ($user->account_type) {
            'seller' => redirect()->route('seller.dashboard'),
            'logistics' => redirect()->route('logistics.dashboard'),
            default => redirect('/homepage'), // Buyers go to homepage
        };
    })->name('dashboard');
    
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
    
    // Buyer Settings/Account Page
    Route::get('/settings', function () {
        return Inertia::render('BuyerAcc');
    });
    
    // Seller Dashboard - for approved sellers only
    Route::get('/seller/dashboard', [\App\Http\Controllers\SellerDashboardController::class, 'index'])->name('seller.dashboard');
    
    // Logistics Dashboard - for approved logistics users only
    Route::get('/logistics/dashboard', function () {
        $user = auth()->user();
        
        return Inertia::render('logistics/LogisticsDashboard', [
            'auth' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'account_type' => $user->account_type,
                ],
            ],
            'company' => [
                'name' => $user->company_name ?? $user->business_name,
                'contact_person' => trim($user->contact_first_name . ' ' . $user->contact_last_name),
            ],
        ]);
    })->name('logistics.dashboard');
    
    // Courier Dashboard - for approved courier/logistics users ONLY
    Route::get('/courier/dashboard', function () {
        $user = auth()->user();
        
        // Access control: Only approved courier/logistics accounts can access
        if ($user->account_type !== 'logistics' || $user->status !== 'approved') {
            abort(403, 'Access denied. Only approved courier companies can access this dashboard.');
        }
        
        // Mock stats for now - replace with real data later
        $stats = [
            'incoming_shipments' => 15,
            'pending_pickups' => 8,
            'in_transit' => 42,
            'deliveries_today' => 23,
            'exceptions' => 3,
        ];
        
        return Inertia::render('courier/CourierDashboard', [
            'auth' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'account_type' => $user->account_type,
                ],
            ],
            'company' => [
                'name' => $user->company_name ?? $user->business_name ?? 'Courier Company',
                'contact_person' => trim(($user->contact_first_name ?? '') . ' ' . ($user->contact_last_name ?? '')),
            ],
            'stats' => $stats,
        ]);
    })->name('courier.dashboard');
    
    // Logistics Incoming Parcels
    Route::get('/logistics/incoming-parcels', function () {
        $user = auth()->user();
        
        // Fetch orders with status "shipped" (scanned QR + confirmed, awaiting pickup from logistics)
        $incomingOrders = \App\Models\Order::where('status', 'shipped')
            ->with(['items.product'])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($order) {
                // Get buyer info
                $buyer = \App\Models\User::find($order->buyer_id);
                
                // Get seller info from first item's product
                $sellerName = 'N/A';
                if ($order->items->isNotEmpty()) {
                    $product = $order->items->first()->product;
                    if ($product && $product->seller_id) {
                        $seller = \App\Models\User::find($product->seller_id);
                        $sellerName = $seller ? ($seller->business_name ?? $seller->name) : 'N/A';
                    }
                }
                
                // Normalize payment method (database stores "cod", "gcash", etc. in lowercase)
                $paymentMethod = strtolower($order->payment_method ?? 'cod');
                $paymentMethodDisplay = match($paymentMethod) {
                    'cod' => 'Cash on Delivery',
                    'gcash' => 'GCash',
                    'paymaya' => 'PayMaya',
                    'card' => 'Credit/Debit Card',
                    default => 'Cash on Delivery',
                };
                
                // Compute estimated weight (0.3kg per item if no weight in database)
                $estimatedWeight = $order->items->sum(function ($item) {
                    return $item->quantity * 0.3; // Assume 0.3kg per item
                });
                
                return [
                    'id' => $order->id,
                    'order_number' => 'MIM-' . str_pad($order->id, 8, '0', STR_PAD_LEFT),
                    'waybill_number' => $order->waybill_number,
                    'buyer_name' => $buyer->name ?? $order->shipping_name ?? 'N/A',
                    'buyer_phone' => $order->shipping_phone ?? 'N/A',
                    'buyer_address' => $order->shipping_address ?? 'N/A',
                    'seller_name' => $sellerName,
                    'total_amount' => $order->total ?? 0,
                    'payment_method' => $paymentMethodDisplay,
                    'items_count' => $order->items->count(),
                    'weight' => round($estimatedWeight, 1),
                    'shipped_at' => $order->updated_at->format('M d, Y H:i'),
                    'status' => 'shipped',
                ];
            });
        
        return Inertia::render('logistics/IncomingParcels', [
            'auth' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'account_type' => $user->account_type,
                ],
            ],
            'company' => [
                'name' => $user->company_name ?? $user->business_name,
                'contact_person' => trim($user->contact_first_name . ' ' . $user->contact_last_name),
            ],
            'incomingParcels' => $incomingOrders,
        ]);
    })->name('logistics.incoming-parcels');
    
    // API: Get parcel details by waybill number (for scanner)
    Route::get('/api/logistics/parcel/{waybillNumber}', function ($waybillNumber) {
        // Debug: Log the scanned waybill number
        \Log::info('Scanned waybill number: ' . $waybillNumber);
        
        $order = \App\Models\Order::where('waybill_number', $waybillNumber)
            ->with(['items.product']) // Just load product, we'll get seller manually
            ->first();
        
        // Debug: Log all waybill numbers in database
        $allWaybills = \App\Models\Order::whereNotNull('waybill_number')
            ->pluck('waybill_number', 'id');
        \Log::info('All waybill numbers in DB:', $allWaybills->toArray());
        
        if (!$order) {
            return response()->json([
                'error' => 'Parcel not found',
                'scanned' => $waybillNumber,
                'available_waybills' => $allWaybills->values(),
            ], 404);
        }
        
        // Get buyer info
        $buyer = \App\Models\User::find($order->buyer_id);
        
        // Get seller info from first item's product
        $sellerName = 'N/A';
        if ($order->items->isNotEmpty()) {
            $product = $order->items->first()->product;
            if ($product && $product->seller_id) {
                $seller = \App\Models\User::find($product->seller_id);
                $sellerName = $seller ? ($seller->business_name ?? $seller->name) : 'N/A';
            }
        }
        
        // Normalize payment method (database stores "cod", "gcash", etc. in lowercase)
        $paymentMethod = strtolower($order->payment_method ?? 'cod');
        $paymentMethodDisplay = match($paymentMethod) {
            'cod' => 'Cash on Delivery',
            'gcash' => 'GCash',
            'paymaya' => 'PayMaya',
            'card' => 'Credit/Debit Card',
            default => 'Cash on Delivery',
        };
        
        // Compute estimated weight (0.3kg per item)
        $estimatedWeight = $order->items->sum(function ($item) {
            return $item->quantity * 0.3;
        });
        
        return response()->json([
            'parcel' => [
                'id' => $order->id,
                'order_number' => 'MIM-' . str_pad($order->id, 8, '0', STR_PAD_LEFT),
                'waybill_number' => $order->waybill_number,
                'buyer_name' => $buyer->name ?? $order->shipping_name ?? 'N/A',
                'buyer_phone' => $order->shipping_phone ?? 'N/A',
                'buyer_address' => $order->shipping_address ?? 'N/A',
                'seller_name' => $sellerName,
                'total_amount' => $order->total,
                'payment_method' => $paymentMethodDisplay,
                'items_count' => $order->items->count(),
                'weight' => round($estimatedWeight, 1),
                'shipped_at' => $order->updated_at->toISOString(),
                'status' => $order->status,
            ],
        ]);
    });
    
    // DEBUG ROUTE: Check waybill numbers in database
    Route::get('/api/debug/waybills', function () {
        $orders = \App\Models\Order::whereNotNull('waybill_number')
            ->select('id', 'waybill_number', 'status', 'waybill_generated_at', 'waybill_printed_at')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => 'MIM-' . str_pad($order->id, 8, '0', STR_PAD_LEFT),
                    'waybill_number' => $order->waybill_number,
                    'status' => $order->status,
                    'waybill_generated_at' => $order->waybill_generated_at,
                    'waybill_printed_at' => $order->waybill_printed_at,
                ];
            });
        
        return response()->json([
            'count' => $orders->count(),
            'orders' => $orders,
        ]);
    });
    
    // API: Confirm parcel received at logistics hub (packed → shipped)
    Route::post('/api/logistics/parcel/{id}/confirm', function ($id) {
        $order = \App\Models\Order::findOrFail($id);
        
        // Validate that order is in packed status (ready for pickup)
        if ($order->status !== 'packed') {
            return response()->json([
                'error' => 'Order is not ready for pickup',
                'current_status' => $order->status,
            ], 400);
        }
        
        // Update order status to shipped (in transit to buyer)
        $order->update(['status' => 'shipped']);
        
        \Log::info('Parcel confirmed received at logistics hub', [
            'order_id' => $order->id,
            'waybill_number' => $order->waybill_number,
            'confirmed_by' => auth()->id(),
            'confirmed_at' => now(),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Parcel confirmed! Now in transit.',
            'order' => [
                'id' => $order->id,
                'waybill_number' => $order->waybill_number,
                'status' => $order->status,
            ],
        ]);
    });
    
    // API: Report problem with parcel
    Route::post('/api/logistics/parcel/{id}/report', function (Illuminate\Http\Request $request, $id) {
        $order = \App\Models\Order::findOrFail($id);
        
        $reason = $request->input('reason', '');
        
        if (empty($reason)) {
            return response()->json([
                'error' => 'Problem reason is required',
            ], 400);
        }
        
        // Log the problem
        \Log::warning('Parcel problem reported', [
            'order_id' => $order->id,
            'waybill_number' => $order->waybill_number,
            'status' => $order->status,
            'reason' => $reason,
            'reported_by' => auth()->id(),
            'reported_at' => now(),
        ]);
        
        // TODO: In future, you might want to:
        // 1. Store problem reports in a separate table
        // 2. Send notification to seller
        // 3. Update order status to 'on_hold' or similar
        // 4. Create a ticket in support system
        
        return response()->json([
            'success' => true,
            'message' => 'Problem reported. Seller will be notified.',
            'order' => [
                'id' => $order->id,
                'waybill_number' => $order->waybill_number,
                'status' => $order->status,
            ],
        ]);
    });
    
    // Logistics Courier Applications
    Route::get('/logistics/courier-applications', function () {
        $user = auth()->user();
        
        // Fetch real courier applications from users table
        $applications = \App\Models\User::where('account_type', 'logistics')
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($courier) {
                // Build full name
                $fullName = trim(($courier->first_name ?? '') . ' ' . ($courier->last_name ?? ''));
                if (empty($fullName)) {
                    $fullName = $courier->name;
                }
                
                // Get company name with fallbacks
                $companyName = $courier->company_name 
                    ?? $courier->trade_name 
                    ?? $courier->business_name 
                    ?? $fullName 
                    ?? 'Unnamed Company';
                
                return [
                    'id' => $courier->id,
                    'company_name' => $companyName,
                    'trade_name' => $courier->trade_name ?? $courier->company_name ?? $companyName,
                    'business_email' => $courier->email,
                    'rep_full_name' => $fullName,
                    'rep_position' => $courier->contact_position ?? 'Representative',
                    'status' => $courier->status, // pending, approved, rejected
                    'submitted_at' => $courier->created_at->format('M d, Y'),
                    'contact_number' => $courier->contact_number,
                ];
            });
        
        return Inertia::render('logistics/CourierApplications', [
            'auth' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'account_type' => $user->account_type,
                ],
            ],
            'applications' => $applications,
            'debug' => [
                'total_count' => $applications->count(),
                'timestamp' => now()->toDateTimeString(),
            ],
        ]);
    })->name('logistics.courier-applications');
    
    // View single courier application details
    Route::get('/logistics/courier-applications/{id}', function ($id) {
        $user = auth()->user();
        
        $courier = \App\Models\User::where('account_type', 'logistics')
            ->where('id', $id)
            ->firstOrFail();
        
        // Decode service_areas JSON
        $serviceAreas = [];
        if ($courier->service_areas) {
            $decoded = json_decode($courier->service_areas, true);
            $serviceAreas = is_array($decoded) ? $decoded : [];
        }
        
        // Parse service areas into regions, provinces, cities
        $regions = [];
        $provinces = [];
        $cities = [];
        
        foreach ($serviceAreas as $area) {
            // Simple heuristic: regions have "Region" in name, cities have "City" or common city names
            if (stripos($area, 'Region') !== false || stripos($area, 'NCR') !== false || stripos($area, 'CAR') !== false || stripos($area, 'BARMM') !== false || stripos($area, 'MIMAROPA') !== false) {
                $regions[] = $area;
            } elseif (stripos($area, 'City') !== false || stripos($area, 'Municipality') !== false || stripos($area, 'Manila') !== false || stripos($area, 'Quezon') !== false || stripos($area, 'Makati') !== false || stripos($area, 'Taguig') !== false || stripos($area, 'Pasig') !== false) {
                $cities[] = $area;
            } else {
                // Likely a province
                $provinces[] = $area;
            }
        }
        
        // Build full name
        $fullName = trim(($courier->first_name ?? '') . ' ' . ($courier->last_name ?? ''));
        if (empty($fullName)) {
            $fullName = $courier->name ?? 'Unknown';
        }
        
        $application = [
            // Basic info
            'id' => $courier->id,
            'status' => $courier->status ?? 'pending',
            'submitted_at' => $courier->created_at->format('F d, Y g:i A'),
            'updated_at' => $courier->updated_at->format('F d, Y g:i A'),
            'approved_at' => $courier->approved_at ? $courier->approved_at->format('F d, Y g:i A') : null,
            
            // Step 1: Company Information
            'company_name' => $courier->company_name ?? $courier->trade_name ?? $fullName,
            'trade_name' => $courier->trade_name ?? $courier->company_name,
            'business_registration_number' => $courier->sec_registration_no,
            'business_address' => $courier->street_address,
            'business_email' => $courier->email,
            'company_contact_number' => $courier->contact_number,
            'company_website' => $courier->company_website,
            'company_logo' => $courier->company_logo,
            
            // Step 2: Authorized Representative
            'rep_full_name' => $fullName,
            'rep_first_name' => $courier->first_name ?? $courier->contact_first_name,
            'rep_last_name' => $courier->last_name ?? $courier->contact_last_name,
            'rep_position' => $courier->contact_position ?? 'Representative',
            'rep_email' => $courier->email,
            'rep_contact_number' => $courier->contact_number,
            'rep_sex' => $courier->sex ?? 'Not specified',
            'rep_birthday' => $courier->birthday,
            
            // Step 3: Business Documents
            'dti_sec_certificate' => $courier->sec_certificate,
            'mayors_permit' => $courier->business_permit,
            'operating_permits' => $courier->bir_2303,
            'rep_valid_id' => $courier->valid_id,
            'id_document' => $courier->id_document,
            'proof_of_address' => $courier->proof_of_address,
            
            // Step 4: Service Coverage
            'regions_served' => $regions,
            'provinces_served' => $provinces,
            'cities_served' => $cities,
            'service_areas_raw' => $serviceAreas,
            'delivery_services' => $courier->delivery_services,
            'pickup_available' => $courier->pickup_available,
            'estimated_delivery_time' => $courier->estimated_delivery_time,
            
            // Address details
            'region' => $courier->region ?? 'Not specified',
            'province' => $courier->province,
            'municipality_city' => $courier->municipality_city ?? 'Not specified',
            'barangay' => $courier->barangay ?? 'Not specified',
            'postal_code' => $courier->postal_code,
            
            // Other
            'privacy_policy_accepted' => $courier->privacy_policy_accepted ?? false,
            'verification_status' => $courier->verification_status ?? 'unverified',
        ];
        
        return Inertia::render('logistics/CourierApplicationDetail', [
            'auth' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'account_type' => $user->account_type,
                ],
            ],
            'application' => $application,
        ]);
    })->name('logistics.courier-application-detail');
    
    // API: Approve courier application
    Route::post('/api/logistics/courier-applications/{id}/approve', function ($id) {
        $courier = \App\Models\User::findOrFail($id);
        
        // Validate that this is a logistics account
        if ($courier->account_type !== 'logistics') {
            return response()->json([
                'error' => 'This account is not a courier/logistics account',
            ], 400);
        }
        
        // Validate that it's pending
        if ($courier->status !== 'pending') {
            return response()->json([
                'error' => 'Application is not pending',
                'current_status' => $courier->status,
            ], 400);
        }
        
        // Approve the courier
        $courier->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);
        
        \Log::info('Courier application approved', [
            'courier_id' => $courier->id,
            'company_name' => $courier->business_name ?? $courier->name,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        
        // TODO: Send email notification to courier about approval
        
        return response()->json([
            'success' => true,
            'message' => 'Courier application approved successfully',
            'courier' => [
                'id' => $courier->id,
                'status' => $courier->status,
                'approved_at' => $courier->approved_at,
            ],
        ]);
    });
    
    // API: Reject courier application
    Route::post('/api/logistics/courier-applications/{id}/reject', function (Illuminate\Http\Request $request, $id) {
        $courier = \App\Models\User::findOrFail($id);
        
        // Validate that this is a logistics account
        if ($courier->account_type !== 'logistics') {
            return response()->json([
                'error' => 'This account is not a courier/logistics account',
            ], 400);
        }
        
        // Validate that it's pending
        if ($courier->status !== 'pending') {
            return response()->json([
                'error' => 'Application is not pending',
                'current_status' => $courier->status,
            ], 400);
        }
        
        $reason = $request->input('reason', 'Application rejected by logistics team');
        
        // Reject the courier
        $courier->update([
            'status' => 'rejected',
        ]);
        
        \Log::info('Courier application rejected', [
            'courier_id' => $courier->id,
            'company_name' => $courier->business_name ?? $courier->name,
            'reason' => $reason,
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
        ]);
        
        // TODO: Send email notification to courier about rejection with reason
        
        return response()->json([
            'success' => true,
            'message' => 'Courier application rejected',
            'courier' => [
                'id' => $courier->id,
                'status' => $courier->status,
            ],
        ]);
    });
    
    // Logistics Sort & Assign
    Route::get('/logistics/sort-assign', function () {
        $user = auth()->user();
        
        // Fetch orders with status "shipped" (confirmed at logistics hub, ready for sorting)
        $transitOrders = \App\Models\Order::where('status', 'shipped')
            ->with(['items.product'])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($order) {
                // Get buyer info
                $buyer = \App\Models\User::find($order->buyer_id);
                
                // Get seller info from first item's product
                $sellerName = 'N/A';
                if ($order->items->isNotEmpty()) {
                    $product = $order->items->first()->product;
                    if ($product && $product->seller_id) {
                        $seller = \App\Models\User::find($product->seller_id);
                        $sellerName = $seller ? ($seller->business_name ?? $seller->name) : 'N/A';
                    }
                }
                
                // Extract barangay from shipping address
                $address = $order->shipping_address ?? '';
                $barangay = 'Unknown';
                if (preg_match('/Brgy\.?\s+([^,]+)/i', $address, $matches)) {
                    $barangay = trim($matches[1]);
                } elseif (preg_match('/Barangay\s+([^,]+)/i', $address, $matches)) {
                    $barangay = trim($matches[1]);
                }
                
                // Normalize payment method (database stores "cod", "gcash", etc. in lowercase)
                $paymentMethod = strtolower($order->payment_method ?? 'cod');
                $paymentMethodDisplay = match($paymentMethod) {
                    'cod' => 'Cash on Delivery',
                    'gcash' => 'GCash',
                    'paymaya' => 'PayMaya',
                    'card' => 'Credit/Debit Card',
                    default => 'Cash on Delivery',
                };
                
                // Compute estimated weight (0.3kg per item)
                $estimatedWeight = $order->items->sum(function ($item) {
                    return $item->quantity * 0.3;
                });
                
                return [
                    'id' => $order->id,
                    'order_number' => 'MIM-' . str_pad($order->id, 8, '0', STR_PAD_LEFT),
                    'waybill_number' => $order->waybill_number,
                    'buyer_name' => $buyer->name ?? $order->shipping_name ?? 'N/A',
                    'buyer_phone' => $order->shipping_phone ?? 'N/A',
                    'buyer_address' => $order->shipping_address ?? 'N/A',
                    'barangay' => $barangay,
                    'seller_name' => $sellerName,
                    'total_amount' => $order->total ?? 0,
                    'payment_method' => $paymentMethodDisplay,
                    'items_count' => $order->items->count(),
                    'weight' => round($estimatedWeight, 1),
                    'confirmed_at' => $order->updated_at->format('M d, Y H:i'),
                    'status' => 'shipped',
                ];
            });
        
        // Fetch approved courier/logistics companies with their service areas
        $approvedCouriers = \App\Models\User::where('account_type', 'logistics')
            ->where('status', 'approved')
            ->orderBy('company_name')
            ->get()
            ->map(function ($courier) {
                // Parse service_areas JSON (array of barangays/municipalities the courier services)
                $serviceAreas = [];
                if ($courier->service_areas) {
                    $serviceAreas = is_string($courier->service_areas) 
                        ? json_decode($courier->service_areas, true) 
                        : $courier->service_areas;
                }
                
                return [
                    'id' => $courier->id,
                    'name' => $courier->company_name ?? $courier->business_name ?? $courier->name,
                    'contact_person' => trim(($courier->contact_first_name ?? '') . ' ' . ($courier->contact_last_name ?? '')),
                    'email' => $courier->email,
                    'service_areas' => $serviceAreas ?? [], // Array of barangays/cities they service
                    'region' => $courier->region,
                    'province' => $courier->province,
                    'municipality_city' => $courier->municipality_city,
                ];
            });
        
        return Inertia::render('logistics/SortAssign', [
            'auth' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'account_type' => $user->account_type,
                ],
            ],
            'company' => [
                'name' => $user->company_name ?? $user->business_name,
                'contact_person' => trim($user->contact_first_name . ' ' . $user->contact_last_name),
            ],
            'parcels' => $transitOrders,
            'couriers' => $approvedCouriers,
        ]);
    })->name('logistics.sort-assign');
    
    // Logistics Delivery Monitoring
    Route::get('/logistics/delivery-monitoring', function () {
        $user = auth()->user();
        
        return Inertia::render('logistics/DeliveryMonitoring', [
            'auth' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'account_type' => $user->account_type,
                ],
            ],
            'company' => [
                'name' => $user->company_name ?? $user->business_name,
                'contact_person' => trim($user->contact_first_name . ' ' . $user->contact_last_name),
            ],
        ]);
    })->name('logistics.delivery-monitoring');
    
    // Logistics Riders
    Route::get('/logistics/riders', function () {
        $user = auth()->user();
        
        return Inertia::render('logistics/Riders', [
            'auth' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'account_type' => $user->account_type,
                ],
            ],
            'company' => [
                'name' => $user->company_name ?? $user->business_name,
                'contact_person' => trim($user->contact_first_name . ' ' . $user->contact_last_name),
            ],
        ]);
    })->name('logistics.riders');
    
    // Logistics Branches & Checklists
    Route::get('/logistics/branches-checklists', function () {
        $user = auth()->user();
        
        return Inertia::render('logistics/BranchesChecklists', [
            'auth' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'account_type' => $user->account_type,
                ],
            ],
            'company' => [
                'name' => $user->company_name ?? $user->business_name,
                'contact_person' => trim($user->contact_first_name . ' ' . $user->contact_last_name),
            ],
        ]);
    })->name('logistics.branches-checklists');
    
    // Logistics Zones & Rates
    Route::get('/logistics/zones-rates', function () {
        $user = auth()->user();
        
        return Inertia::render('logistics/ZonesRates', [
            'auth' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'account_type' => $user->account_type,
                ],
            ],
            'company' => [
                'name' => $user->company_name ?? $user->business_name,
                'contact_person' => trim($user->contact_first_name . ' ' . $user->contact_last_name),
            ],
        ]);
    })->name('logistics.zones-rates');
    
    // Logistics COD & Remittance
    Route::get('/logistics/cod-remittance', function () {
        $user = auth()->user();
        
        return Inertia::render('logistics/CODRemittance', [
            'auth' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'account_type' => $user->account_type,
                ],
            ],
            'company' => [
                'name' => $user->company_name ?? $user->business_name,
                'contact_person' => trim($user->contact_first_name . ' ' . $user->contact_last_name),
            ],
        ]);
    })->name('logistics.cod-remittance');
    
    // Logistics Reports
    Route::get('/logistics/reports', function () {
        $user = auth()->user();
        
        return Inertia::render('logistics/Reports', [
            'auth' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'account_type' => $user->account_type,
                ],
            ],
            'company' => [
                'name' => $user->company_name ?? $user->business_name,
                'contact_person' => trim($user->contact_first_name . ' ' . $user->contact_last_name),
            ],
        ]);
    })->name('logistics.reports');
    
    // Logistics Support
    Route::get('/logistics/support', function () {
        $user = auth()->user();
        
        return Inertia::render('logistics/Support', [
            'auth' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'account_type' => $user->account_type,
                ],
            ],
            'company' => [
                'name' => $user->company_name ?? $user->business_name,
                'contact_person' => trim($user->contact_first_name . ' ' . $user->contact_last_name),
            ],
        ]);
    })->name('logistics.support');
    
    // Logistics Messages
    Route::get('/logistics/messages', function () {
        $user = auth()->user();
        
        return Inertia::render('logistics/Messages', [
            'auth' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'account_type' => $user->account_type,
                ],
            ],
            'company' => [
                'name' => $user->company_name ?? $user->business_name,
                'contact_person' => trim($user->contact_first_name . ' ' . $user->contact_last_name),
            ],
        ]);
    })->name('logistics.messages');
    
    // Logistics Account
    Route::get('/logistics/account', function () {
        $user = auth()->user();
        
        return Inertia::render('logistics/Account', [
            'auth' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'account_type' => $user->account_type,
                ],
            ],
            'company' => [
                'name' => $user->company_name ?? $user->business_name,
                'contact_person' => trim($user->contact_first_name . ' ' . $user->contact_last_name),
            ],
        ]);
    })->name('logistics.account');
    
    // Seller Orders Page
    Route::get('/seller/orders', function () {
        // Don't load orders here - let the frontend fetch via API
        return Inertia::render('seller/SellerOrders', [
            'orders' => [], // Empty - frontend will fetch
        ]);
    })->name('seller.orders');
    
    // Seller Products page
    Route::get('/seller/products', function () {
        $user = Auth::user();
        
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
            'products' => [], // Empty - let frontend fetch via API for faster navigation
        ]);
    })->name('seller.products');
    
    // Add Product page
    Route::get('/seller/products/create', function () {
        return Inertia::render('seller/ProductForm');
    })->name('seller.products.create');
    
    // Edit Product page
    Route::get('/seller/products/{id}/edit', function ($id) {
        $product = \App\Models\Product::findOrFail($id);
        
        // Check if product belongs to this seller
        if ($product->seller_id !== auth()->id()) {
            abort(403);
        }
        
        return Inertia::render('seller/ProductForm', [
            'product' => [
                'id' => (string) $product->id,
                'name' => $product->name,
                'category' => $product->category ?? '',
                'brand' => $product->brand ?? '',
                'description' => $product->description ?? '',
                'images' => $product->images ?? [],
                'mainIdx' => $product->main_image_index ?? 0,
                'price' => (float) $product->price,
                'salePrice' => $product->sale_price ? (float) $product->sale_price : null,
                'stock' => $product->stock,
                'sku' => $product->sku,
                'weight' => $product->weight ? (float) $product->weight : null,
                'status' => $product->status === 'active' ? 'published' : 'draft', // Convert active → published
                'has_variants' => $product->has_variants ?? false,
                'variant_options' => $product->variant_options ?? [],
                'variants' => $product->variants ?? [],
            ],
        ]);
    })->name('seller.products.edit');
    
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
    
    // Seller Customers page
    Route::get('/seller/customers', function () {
        return Inertia::render('seller/SellerCustomers');
    })->name('seller.customers');
    
    // Seller Analytics page
    Route::get('/seller/analytics', function () {
        return Inertia::render('seller/SellerAnalytics');
    })->name('seller.analytics');
    
    // Seller Messages page
    Route::get('/seller/messages', function () {
        return Inertia::render('seller/SellerMessages');
    })->name('seller.messages');
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

Route::get('/register/courier', function () {
    // Force logout if already authenticated
    if (Auth::check()) {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
    return Inertia::render('logistics/CourierRegistration');
})->name('register.courier');

Route::get('/register/rider', function () {
    return Inertia::render('logistics/RiderRegistration');
})->name('register.rider');

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

// Seller pending approval page
Route::get('/seller/pending-approval', function () {
    return Inertia::render('seller/PendingApproval');
})->name('seller.pending-approval');

// Logistics pending approval page
Route::get('/logistics/pending-approval', function () {
    return Inertia::render('logistics/PendingApproval');
})->name('logistics.pending-approval');

// Privacy Policy page
Route::get('/privacy-policy', function () {
    return Inertia::render('PrivacyPolicy');
})->name('privacy.policy');

// Terms page (assuming you already have this)
Route::get('/terms', function () {
    return Inertia::render('Terms');
})->name('terms');

Route::get('/homepage', function (Illuminate\Http\Request $request) {
    // Base query for products
    $query = \App\Models\Product::with('seller:id,business_name,name')
        ->where('status', 'active')
        ->where('approval_status', 'approved');
    
    // Apply search filter
    if ($request->has('search') && $request->input('search') !== '') {
        $search = $request->input('search');
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('category', 'like', "%{$search}%")
                ->orWhere('brand', 'like', "%{$search}%");
        });
    }
    
    // Apply category filter
    if ($request->has('category') && $request->input('category') !== '') {
        $query->where('category', $request->input('category'));
    }
    
    // Fetch top-selling products from the last 7 days with applied filters
    $products = $query->withCount(['orderItems as total_sold' => function ($query) {
            $query->selectRaw('COALESCE(SUM(quantity), 0)')
                ->whereHas('order', function ($q) {
                    $q->where('status', '!=', 'cancelled')
                        ->where('status', '!=', 'failed')
                        ->where('created_at', '>=', now()->subDays(7));
                });
        }])
        ->orderBy('total_sold', 'desc')
        ->orderBy('created_at', 'desc')
        ->take(50) // Increased to 50 for better filtering results
        ->get()
        ->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'sale_price' => $product->sale_price ? (float) $product->sale_price : null,
                'effective_price' => (float) ($product->sale_price ?? $product->price),
                'is_on_sale' => $product->sale_price !== null,
                'stock' => $product->stock,
                'stock_status' => $product->stock_status,
                'is_in_stock' => $product->stock > 0,
                'is_low_stock' => $product->stock > 0 && $product->stock <= ($product->low_stock_threshold ?? 5),
                'main_image' => $product->main_image,
                'seller_name' => $product->seller->business_name ?? $product->seller->name,
                'category' => $product->category,
                'brand' => $product->brand,
                'total_sold' => $product->total_sold ?? 0,
            ];
        });
    
    return Inertia::render('Homepage', [
        'initialProducts' => $products,
        'filters' => [
            'search' => $request->input('search', ''),
            'category' => $request->input('category', ''),
        ],
        'vouchers' => \App\Models\Voucher::where('status', 'active')
            ->where('valid_from', '<=', now())
            ->where('valid_until', '>=', now())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($voucher) use ($request) {
                $user = $request->user();
                
                // Check if user has already claimed this voucher
                $userClaim = $user ? \App\Models\VoucherUsage::where('voucher_id', $voucher->id)
                    ->where('user_id', $user->id)
                    ->first() : null;
                
                return [
                    'id' => $voucher->id,
                    'code' => $voucher->code,
                    'name' => $voucher->name,
                    'description' => $voucher->description,
                    'type' => $voucher->type,
                    'value' => $voucher->value,
                    'min_purchase' => $voucher->min_purchase,
                    'max_discount' => $voucher->max_discount,
                    'valid_until_formatted' => $voucher->valid_until->format('M d, Y'),
                    'is_claimed' => $userClaim !== null,
                    'is_used' => $userClaim && $userClaim->used_at !== null,
                    'claimed_at' => $userClaim?->claimed_at?->format('M d, Y'),
                    'used_at' => $userClaim?->used_at?->format('M d, Y'),
                ];
            }),
    ]);
})->name('homepage');

Route::get('/messages', function () {
    return view('messages');
})->middleware(['auth', 'approved'])->name('messages');

// Buyer checkout and order routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/cart', function () {
        // Fetch suggested products (top-selling or random)
        $suggestedProducts = \App\Models\Product::where('status', 'active')
            ->where('stock', '>', 0)
            ->inRandomOrder()
            ->limit(6)
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
                    'seller_name' => $product->seller->business_name ?? $product->seller->name,
                ];
            });
        
        return Inertia::render('Cart', [
            'suggestedProducts' => $suggestedProducts,
        ]);
    })->name('cart');
    
    Route::get('/checkout', [\App\Http\Controllers\OrderController::class, 'checkout'])->name('checkout');
    Route::get('/orders', [\App\Http\Controllers\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/success', [\App\Http\Controllers\OrderController::class, 'success'])->name('orders.success');
    Route::get('/orders/{id}', [\App\Http\Controllers\OrderController::class, 'show'])->name('orders.show');
});

Route::get('/product/{id}', function (string $id) {
    $product = \App\Models\Product::with('seller:id,business_name,name,profile_photo')
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
            'seller_profile_photo' => $product->seller->profile_photo ? asset('storage/' . $product->seller->profile_photo) : null,
            'has_variants' => $product->has_variants ?? false,
            'variant_options' => $product->variant_options ?? null,
            'variants' => $product->variants ?? null,
        ],
    ]);
})->name('product.show');

Route::get('/seller/{id}', function (string $id) {
    $seller = \App\Models\User::where('id', $id)
        ->where('account_type', 'seller')
        ->firstOrFail();
    
    $sellerData = [
        'id' => $seller->id,
        'name' => $seller->name,
        'business_name' => $seller->business_name,
        'email' => $seller->email,
        'profile_photo_url' => $seller->profile_photo ? asset('storage/' . $seller->profile_photo) : null,
        'cover_photo_url' => $seller->cover_photo ? asset('storage/' . $seller->cover_photo) : null,
    ];
    
    // Debug logging
    \Log::info('ViewSeller data:', $sellerData);
    
    return Inertia::render('ViewSeller', [
        'seller' => $sellerData,
    ]);
})->name('seller.view');

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

// Waybill QR code (public for scanning)
Route::get('/waybill/{waybillNumber}/qr', [\App\Http\Controllers\WaybillController::class, 'qrCode'])->name('waybill.qr');

// ---------------------------------------------------------------------------
// Product API routes
// ---------------------------------------------------------------------------

// Public product routes (buyers) - upgraded with search, filter, sort, pagination
Route::get('/api/products', [ProductController::class, 'index']); // List active products
Route::get('/api/products/{id}', [ProductController::class, 'show']); // Single product

// Public seller store data
Route::get('/api/seller/{id}/store', [ProductController::class, 'getSellerStore']); // Seller store with products and stats

// Protected buyer routes - Cart & Orders API
Route::middleware(['auth', 'verified'])->group(function () {
    // Cart
    Route::get('/api/cart', [\App\Http\Controllers\CartController::class, 'index']);
    Route::get('/api/cart/count', [\App\Http\Controllers\CartController::class, 'getCount']);
    Route::post('/api/cart/items', [\App\Http\Controllers\CartController::class, 'addItem']);
    Route::patch('/api/cart/items/{id}', [\App\Http\Controllers\CartController::class, 'updateItem']);
    Route::delete('/api/cart/items/{id}', [\App\Http\Controllers\CartController::class, 'removeItem']);
    
    // Orders
    Route::post('/api/orders', [\App\Http\Controllers\OrderController::class, 'store']);
    Route::post('/api/orders/{id}/cancel', [\App\Http\Controllers\OrderController::class, 'cancel']);
    
    // Voucher validation
    Route::post('/api/vouchers/validate', [\App\Http\Controllers\Admin\VoucherController::class, 'validate']);
    Route::get('/api/vouchers/available', [\App\Http\Controllers\Admin\VoucherController::class, 'available']);
    Route::post('/api/vouchers/claim', [\App\Http\Controllers\Admin\VoucherController::class, 'claim']);
    
    
    // Orders data refresh endpoint
    Route::get('/orders/data', [\App\Http\Controllers\OrderController::class, 'getData']);
    
    // Product Reports
    Route::post('/api/products/{id}/report', [ProductController::class, 'reportProduct']);
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
    Route::patch('/api/products/{id}/variants/{variantId}/stock', [ProductController::class, 'updateVariantStock']); // Update variant stock
    Route::delete('/api/products/{id}', [ProductController::class, 'destroy']); // Soft delete product
    
    // Stock logs
    Route::get('/api/products/{id}/stock-logs', [ProductController::class, 'getStockLogs']);
    
    // Seller Orders
    Route::get('/api/seller/orders', [\App\Http\Controllers\SellerOrderController::class, 'index']);
    Route::get('/api/seller/orders/recent', [\App\Http\Controllers\SellerOrderController::class, 'recent']);
    Route::patch('/api/seller/orders/{id}/status', [\App\Http\Controllers\SellerOrderController::class, 'updateStatus']);
    
    // Waybill routes (accessible by seller and logistics)
    Route::post('/api/orders/{id}/waybill/generate', [\App\Http\Controllers\WaybillController::class, 'generate']);
    Route::get('/api/orders/{id}/waybill', [\App\Http\Controllers\WaybillController::class, 'show']);
    Route::post('/api/orders/{id}/waybill/mark-printed', [\App\Http\Controllers\WaybillController::class, 'markPrinted']);
    
    // Real-time seller dashboard stream (SSE)
    Route::get('/api/seller/dashboard/stream', [\App\Http\Controllers\SellerDashboardStreamController::class, 'stream']);
    
    // Store Photos Management
    Route::post('/api/seller/profile-photo', [\App\Http\Controllers\Api\SellerStorePhotoController::class, 'uploadProfilePhoto']);
    Route::post('/api/seller/cover-photo', [\App\Http\Controllers\Api\SellerStorePhotoController::class, 'uploadCoverPhoto']);
    Route::delete('/api/seller/profile-photo', [\App\Http\Controllers\Api\SellerStorePhotoController::class, 'deleteProfilePhoto']);
    Route::delete('/api/seller/cover-photo', [\App\Http\Controllers\Api\SellerStorePhotoController::class, 'deleteCoverPhoto']);
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
        
        // Vouchers Management
        Route::get('/vouchers', [App\Http\Controllers\Admin\VoucherController::class, 'index'])->name('vouchers');
        Route::post('/vouchers', [App\Http\Controllers\Admin\VoucherController::class, 'store']);
        Route::put('/vouchers/{voucher}', [App\Http\Controllers\Admin\VoucherController::class, 'update']);
        Route::delete('/vouchers/{voucher}', [App\Http\Controllers\Admin\VoucherController::class, 'destroy']);
        Route::patch('/vouchers/{voucher}/toggle', [App\Http\Controllers\Admin\VoucherController::class, 'toggleStatus']);
        
        // Banner Management
        Route::get('/banners', function () {
            return Inertia::render('admin/BannerManagement');
        })->name('banners');
        
        Route::get('/manage-registrations', [AdminController::class, 'manageRegistrations'])->name('manage-registrations');
        Route::post('/registrations/{userId}/approve', [AdminController::class, 'approveRegistration'])->name('registrations.approve');
        Route::post('/registrations/{userId}/disapprove', [AdminController::class, 'disapproveRegistration'])->name('registrations.disapprove');
        
        // Seller approval routes
        Route::get('/seller-approval', [AdminController::class, 'sellerApproval'])->name('seller-approval');
        Route::get('/seller-approval/{userId}/details', [AdminController::class, 'getSellerDetails'])->name('seller-approval.details');
        Route::get('/seller-approval/{userId}/download/{type}', [AdminController::class, 'downloadDocument'])->name('download-document');
        Route::post('/seller-approval/{userId}/approve', [AdminController::class, 'approveSellerRegistration'])->name('seller-approval.approve');
        Route::post('/seller-approval/{userId}/disapprove', [AdminController::class, 'disapproveSellerRegistration'])->name('seller-approval.disapprove');
        
        // Logistics approval routes
        Route::get('/logistics-approval', [AdminController::class, 'logisticsApproval'])->name('logistics-approval');
        Route::get('/logistics-approval/{userId}/details', [AdminController::class, 'getLogisticsDetails'])->name('logistics-approval.details');
        Route::post('/logistics-approval/{userId}/approve', [AdminController::class, 'approveLogisticsRegistration'])->name('logistics-approval.approve');
        Route::post('/logistics-approval/{userId}/disapprove', [AdminController::class, 'disapproveLogisticsRegistration'])->name('logistics-approval.disapprove');
        
        // Product approval routes
        Route::get('/product-approval', [AdminController::class, 'productApproval'])->name('product-approval');
        
        // Product reports routes
        Route::get('/reports', [AdminController::class, 'productReports'])->name('reports');
        
        Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
    });
});

// Product approval API routes (outside admin prefix but still protected)
Route::middleware(['web', 'admin.verified'])->prefix('api/admin')->group(function () {
    Route::get('/products/pending', [ProductController::class, 'adminPendingProducts']);
    Route::get('/products', [ProductController::class, 'adminAllProducts']);
    Route::post('/products/{id}/approve', [ProductController::class, 'adminApproveProduct']);
    Route::post('/products/{id}/reject', [ProductController::class, 'adminRejectProduct']);
    
    // Product Reports
    Route::get('/reports', [ProductController::class, 'adminGetReports']);
    Route::post('/reports/{id}/approve', [ProductController::class, 'adminApproveReport']); // Delete product
    Route::post('/reports/{id}/reject', [ProductController::class, 'adminRejectReport']); // Keep product
});