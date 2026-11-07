<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Validator;

echo "=== NCR-ONLY VALIDATION TEST ===\n\n";

// Test 1: NCR with NO provinces (should pass)
echo "Test 1: NCR only, no provinces\n";
$data1 = [
    'account_type' => 'logistics',
    'regions_served' => ['National Capital Region (NCR)'],
    'provinces_served' => [], // Empty
    'cities_served' => ['Manila', 'Quezon City'],
];

$rules = [
    'regions_served' => ['required_if:account_type,logistics', 'nullable', 'array'],
    'regions_served.*' => ['string', 'max:100'],
    'provinces_served' => ['nullable', 'array'],
    'provinces_served.*' => ['string', 'max:100'],
    'cities_served' => ['required_if:account_type,logistics', 'nullable', 'array'],
    'cities_served.*' => ['string', 'max:100'],
];

$validator1 = Validator::make($data1, $rules);

if ($validator1->fails()) {
    echo "  RESULT: FAILED\n";
    echo "  Errors:\n";
    foreach ($validator1->errors()->all() as $error) {
        echo "    - {$error}\n";
    }
} else {
    echo "  RESULT: PASSED ✅\n";
}

echo "\n";

// Test 2: NCR with provinces (should still pass)
echo "Test 2: NCR with provinces\n";
$data2 = [
    'account_type' => 'logistics',
    'regions_served' => ['National Capital Region (NCR)', 'Region III (Central Luzon)'],
    'provinces_served' => ['Bulacan', 'Pampanga'],
    'cities_served' => ['Manila', 'Quezon City', 'Malolos'],
];

$validator2 = Validator::make($data2, $rules);

if ($validator2->fails()) {
    echo "  RESULT: FAILED\n";
    echo "  Errors:\n";
    foreach ($validator2->errors()->all() as $error) {
        echo "    - {$error}\n";
    }
} else {
    echo "  RESULT: PASSED ✅\n";
}

echo "\n";

// Test 3: Empty arrays (should fail - regions and cities required)
echo "Test 3: Empty arrays\n";
$data3 = [
    'account_type' => 'logistics',
    'regions_served' => [],
    'provinces_served' => [],
    'cities_served' => [],
];

$validator3 = Validator::make($data3, $rules);

if ($validator3->fails()) {
    echo "  RESULT: FAILED (as expected)\n";
    echo "  Errors:\n";
    foreach ($validator3->errors()->all() as $error) {
        echo "    - {$error}\n";
    }
} else {
    echo "  RESULT: PASSED\n";
}

echo "\n=== CONCLUSION ===\n";
echo "NCR-only courier (no provinces): " . ($validator1->passes() ? "CAN REGISTER ✅" : "BLOCKED ❌") . "\n";
