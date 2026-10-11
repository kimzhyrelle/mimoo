<?php

/**
 * Concurrent Checkout Test
 * 
 * This script simulates two buyers checking out the last item simultaneously
 * to verify that only one succeeds (no overselling).
 * 
 * SETUP:
 * 1. Create a product with stock = 1
 * 2. Create two buyer accounts
 * 3. Add the product to both buyers' carts with quantity = 1
 * 4. Get CSRF tokens and session cookies for both buyers
 * 5. Run this script
 * 
 * EXPECTED RESULT:
 * - Only ONE buyer's checkout should succeed
 * - The other should get "Insufficient stock" error
 * - Final stock should be 0
 */

// Configuration
$baseUrl = 'http://127.0.0.1:8000';
$buyer1 = [
    'email' => 'buyer1@example.com',
    'password' => 'password',
];
$buyer2 = [
    'email' => 'buyer2@example.com',
    'password' => 'password',
];

// Product ID to test (should have stock = 1)
$productId = 1;

function makeCheckoutRequest($baseUrl, $credentials, $buyerName) {
    echo "\n[$buyerName] Starting checkout...\n";
    
    // Login first to get session
    $ch = curl_init("$baseUrl/login");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIEJAR => "/tmp/cookie_$buyerName.txt",
        CURLOPT_COOKIEFILE => "/tmp/cookie_$buyerName.txt",
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ]),
    ]);
    curl_exec($ch);
    curl_close($ch);
    
    // Get CSRF token
    $ch = curl_init("$baseUrl/checkout");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEFILE => "/tmp/cookie_$buyerName.txt",
    ]);
    $html = curl_exec($ch);
    preg_match('/name="csrf-token" content="([^"]+)"/', $html, $matches);
    $csrfToken = $matches[1] ?? null;
    curl_close($ch);
    
    if (!$csrfToken) {
        echo "[$buyerName] Failed to get CSRF token\n";
        return null;
    }
    
    // Make checkout request
    $ch = curl_init("$baseUrl/api/orders");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEFILE => "/tmp/cookie_$buyerName.txt",
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            "X-CSRF-TOKEN: $csrfToken",
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'payment_method' => 'cod',
            'shipping_name' => $buyerName,
            'shipping_address' => 'Test Address',
            'shipping_phone' => '09123456789',
            'idempotency_key' => uniqid("$buyerName-"),
        ]),
    ]);
    
    $start = microtime(true);
    $response = curl_exec($ch);
    $duration = (microtime(true) - $start) * 1000;
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    echo "[$buyerName] Response (${duration}ms): HTTP $httpCode\n";
    echo "[$buyerName] " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    
    return [
        'success' => $httpCode === 201,
        'response' => $result,
        'duration' => $duration,
    ];
}

// Run concurrent checkouts using parallel processes
echo "=== CONCURRENT CHECKOUT TEST ===\n";
echo "Testing: Two buyers checkout the last item simultaneously\n";

// Fork process for buyer1
$pid1 = pcntl_fork();
if ($pid1 == 0) {
    // Child process - Buyer 1
    makeCheckoutRequest($baseUrl, $buyer1, 'Buyer1');
    exit(0);
}

// Fork process for buyer2
$pid2 = pcntl_fork();
if ($pid2 == 0) {
    // Child process - Buyer 2
    makeCheckoutRequest($baseUrl, $buyer2, 'Buyer2');
    exit(0);
}

// Parent process - wait for both
pcntl_wait($status1);
pcntl_wait($status2);

echo "\n=== TEST COMPLETE ===\n";
echo "Check the output above:\n";
echo "- Only ONE buyer should have succeeded (HTTP 201)\n";
echo "- The other should have failed with 'Insufficient stock' (HTTP 400)\n";
echo "- Verify product stock is now 0 in database\n";
