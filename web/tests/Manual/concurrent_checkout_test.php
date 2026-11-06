<?php

/**
 * Manual Concurrency Test for Checkout System
 * 
 * This script simulates TWO buyers attempting to purchase the LAST item simultaneously.
 * Run this script from command line to test real concurrency behavior.
 * 
 * SETUP:
 * 1. Ensure your Laravel app is running (php artisan serve)
 * 2. Create a product with stock = 1
 * 3. Create two buyer accounts
 * 4. Update the credentials below
 * 5. Run: php tests/Manual/concurrent_checkout_test.php
 * 
 * EXPECTED RESULT:
 * - One buyer succeeds (201 Created)
 * - One buyer fails (400 Bad Request - Insufficient stock)
 * - Product stock becomes 0
 * - Product status becomes 'out_of_stock'
 */

// Configuration
$baseUrl = 'http://127.0.0.1:8000';
$productId = 1; // CHANGE THIS to a product with stock = 1

// Buyer 1 credentials
$buyer1Email = 'buyer1@test.com';
$buyer1Password = 'password';

// Buyer 2 credentials
$buyer2Email = 'buyer2@test.com';
$buyer2Password = 'password';

// Helper function to login and get session cookie
function login($baseUrl, $email, $password) {
    // Get CSRF token
    $ch = curl_init("$baseUrl/login");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    $response = curl_exec($ch);
    curl_close($ch);

    preg_match('/Set-Cookie: XSRF-TOKEN=([^;]+)/', $response, $matches);
    $csrfToken = urldecode($matches[1] ?? '');

    preg_match('/Set-Cookie: laravel_session=([^;]+)/', $response, $matches);
    $sessionCookie = $matches[1] ?? '';

    // Login
    $ch = curl_init("$baseUrl/login");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'email' => $email,
        'password' => $password,
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_COOKIE, "XSRF-TOKEN=$csrfToken; laravel_session=$sessionCookie");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "X-CSRF-TOKEN: $csrfToken",
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    preg_match('/Set-Cookie: laravel_session=([^;]+)/', $response, $matches);
    $authSession = $matches[1] ?? $sessionCookie;

    return ['csrf' => $csrfToken, 'session' => $authSession];
}

// Helper function to add product to cart
function addToCart($baseUrl, $auth, $productId) {
    $ch = curl_init("$baseUrl/api/cart/items");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'product_id' => $productId,
        'quantity' => 1,
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIE, "XSRF-TOKEN={$auth['csrf']}; laravel_session={$auth['session']}");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        "X-CSRF-TOKEN: {$auth['csrf']}",
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['code' => $httpCode, 'body' => json_decode($response, true)];
}

// Helper function to checkout
function checkout($baseUrl, $auth, $buyerName) {
    $ch = curl_init("$baseUrl/api/orders");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'payment_method' => 'cod',
        'shipping_name' => $buyerName,
        'shipping_address' => '123 Test Street',
        'shipping_phone' => '09123456789',
        'idempotency_key' => $buyerName . '-' . microtime(true),
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIE, "XSRF-TOKEN={$auth['csrf']}; laravel_session={$auth['session']}");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        "X-CSRF-TOKEN: {$auth['csrf']}",
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['code' => $httpCode, 'body' => json_decode($response, true)];
}

echo "=== CONCURRENT CHECKOUT TEST ===\n\n";

// Step 1: Login both buyers
echo "Step 1: Logging in buyers...\n";
$auth1 = login($baseUrl, $buyer1Email, $buyer1Password);
$auth2 = login($baseUrl, $buyer2Email, $buyer2Password);
echo "✓ Both buyers logged in\n\n";

// Step 2: Add product to both carts
echo "Step 2: Adding product to both carts...\n";
$cart1 = addToCart($baseUrl, $auth1, $productId);
$cart2 = addToCart($baseUrl, $auth2, $productId);
echo "✓ Product added to Buyer 1 cart: HTTP {$cart1['code']}\n";
echo "✓ Product added to Buyer 2 cart: HTTP {$cart2['code']}\n\n";

// Step 3: Concurrent checkout using multi-curl
echo "Step 3: Simulating concurrent checkout...\n";

$mh = curl_multi_init();

// Buyer 1 checkout
$ch1 = curl_init("$baseUrl/api/orders");
curl_setopt($ch1, CURLOPT_POST, true);
curl_setopt($ch1, CURLOPT_POSTFIELDS, json_encode([
    'payment_method' => 'cod',
    'shipping_name' => 'Buyer 1',
    'shipping_address' => '123 Test Street',
    'shipping_phone' => '09123456789',
    'idempotency_key' => 'buyer1-' . microtime(true),
]));
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch1, CURLOPT_COOKIE, "XSRF-TOKEN={$auth1['csrf']}; laravel_session={$auth1['session']}");
curl_setopt($ch1, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    "X-CSRF-TOKEN: {$auth1['csrf']}",
]);

// Buyer 2 checkout
$ch2 = curl_init("$baseUrl/api/orders");
curl_setopt($ch2, CURLOPT_POST, true);
curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode([
    'payment_method' => 'cod',
    'shipping_name' => 'Buyer 2',
    'shipping_address' => '456 Test Avenue',
    'shipping_phone' => '09987654321',
    'idempotency_key' => 'buyer2-' . microtime(true),
]));
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_COOKIE, "XSRF-TOKEN={$auth2['csrf']}; laravel_session={$auth2['session']}");
curl_setopt($ch2, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    "X-CSRF-TOKEN: {$auth2['csrf']}",
]);

curl_multi_add_handle($mh, $ch1);
curl_multi_add_handle($mh, $ch2);

// Execute all handles
$running = null;
do {
    curl_multi_exec($mh, $running);
    curl_multi_select($mh);
} while ($running > 0);

// Get results
$result1 = curl_multi_getcontent($ch1);
$code1 = curl_getinfo($ch1, CURLINFO_HTTP_CODE);

$result2 = curl_multi_getcontent($ch2);
$code2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);

curl_multi_remove_handle($mh, $ch1);
curl_multi_remove_handle($mh, $ch2);
curl_multi_close($mh);

echo "✓ Concurrent requests completed\n\n";

// Step 4: Display results
echo "=== RESULTS ===\n\n";
echo "Buyer 1: HTTP $code1\n";
echo "Response: " . json_encode(json_decode($result1, true), JSON_PRETTY_PRINT) . "\n\n";

echo "Buyer 2: HTTP $code2\n";
echo "Response: " . json_encode(json_decode($result2, true), JSON_PRETTY_PRINT) . "\n\n";

// Step 5: Verify outcome
echo "=== VERIFICATION ===\n\n";

$success = ($code1 === 201 && $code2 === 400) || ($code1 === 400 && $code2 === 201);

if ($success) {
    echo "✅ TEST PASSED: One buyer succeeded, one failed (expected behavior)\n";
} else {
    echo "❌ TEST FAILED: Both buyers got the same result (unexpected)\n";
}

echo "\nNote: Check your database to verify:\n";
echo "  - Product stock is now 0\n";
echo "  - Product status is 'out_of_stock'\n";
echo "  - Only ONE order exists\n";
echo "  - StockLog has an entry with reason='order'\n";
