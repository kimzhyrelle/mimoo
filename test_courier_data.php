<?php

/**
 * Quick test script to verify courier registration data
 * 
 * Usage:
 *   php test_courier_data.php mreyes@testcourier.ph
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

$email = $argv[1] ?? null;

if (!$email) {
    echo "Usage: php test_courier_data.php <email>\n";
    echo "Example: php test_courier_data.php mreyes@testcourier.ph\n";
    exit(1);
}

echo str_repeat("=", 80) . "\n";
echo "TESTING COURIER DATA FOR: {$email}\n";
echo str_repeat("=", 80) . "\n\n";

// Test 1: Raw DB row
echo "TEST 1: RAW DATABASE ROW\n";
echo str_repeat("-", 80) . "\n";
$rawUser = DB::table('users')->where('email', $email)->first();

if (!$rawUser) {
    echo "❌ User not found with email: {$email}\n";
    exit(1);
}

echo "✅ User found (ID: {$rawUser->id})\n\n";

$newFields = [
    'business_email',
    'username',
    'company_contact_number',
    'rep_contact_number',
    'business_address',
    'rep_full_name',
    'regions_served',
    'provinces_served',
    'cities_served',
    'delivery_services',
    'operating_permits',
];

echo "New Courier Fields:\n";
foreach ($newFields as $field) {
    $value = $rawUser->$field ?? 'NULL';
    if (is_string($value) && strlen($value) > 100) {
        $value = substr($value, 0, 100) . '...';
    }
    echo sprintf("  %-25s: %s\n", $field, $value);
}

echo "\n";

// Test 2: Model with cast
echo "TEST 2: MODEL WITH CASTS (->toArray())\n";
echo str_repeat("-", 80) . "\n";
$user = User::where('email', $email)->first();

if (!$user) {
    echo "❌ User model not found\n";
    exit(1);
}

$userData = $user->toArray();

echo "Array Fields After Cast:\n";
$arrayFields = ['regions_served', 'provinces_served', 'cities_served', 'delivery_services'];
foreach ($arrayFields as $field) {
    $value = $userData[$field] ?? null;
    $type = gettype($value);
    $display = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string)$value;
    
    if (strlen($display) > 100) {
        $display = substr($display, 0, 100) . '...';
    }
    
    $status = is_array($value) ? '✅' : '❌';
    echo sprintf("  %s %-20s (type: %-6s): %s\n", $status, $field, $type, $display);
}

echo "\n";

// Test 3: Check for comma preservation
echo "TEST 3: COMMA PRESERVATION CHECK\n";
echo str_repeat("-", 80) . "\n";
$cities = $userData['cities_served'] ?? [];
$hasComma = false;
$commaItem = null;

if (is_array($cities)) {
    foreach ($cities as $city) {
        if (strpos($city, ',') !== false) {
            $hasComma = true;
            $commaItem = $city;
            break;
        }
    }
}

if ($hasComma) {
    echo "✅ Found city entry with comma preserved: \"{$commaItem}\"\n";
    echo "   This proves custom entries with commas are NOT split!\n";
} else {
    echo "⚠️  No city entry with comma found.\n";
    echo "   If you entered \"Taguig, BGC Area\", it should appear as ONE item.\n";
}

echo "\n";

// Test 4: Comparison table
echo "TEST 4: FIELD COMPARISON\n";
echo str_repeat("-", 80) . "\n";
echo sprintf("%-30s | %-40s\n", "Field", "Value");
echo str_repeat("-", 80) . "\n";

$displayFields = [
    'company_name' => $rawUser->company_name ?? 'NULL',
    'trade_name' => $rawUser->trade_name ?? 'NULL',
    'business_email' => $rawUser->business_email ?? 'NULL',
    'username' => $rawUser->username ?? 'NULL',
    'company_contact_number' => $rawUser->company_contact_number ?? 'NULL',
    'rep_contact_number' => $rawUser->rep_contact_number ?? 'NULL',
    'rep_full_name' => $rawUser->rep_full_name ?? 'NULL',
    'business_address' => $rawUser->business_address ?? 'NULL',
    'regions_served' => is_array($userData['regions_served'] ?? null) 
        ? 'ARRAY[' . count($userData['regions_served']) . ']' 
        : 'NOT_ARRAY',
    'provinces_served' => is_array($userData['provinces_served'] ?? null) 
        ? 'ARRAY[' . count($userData['provinces_served']) . ']' 
        : 'NOT_ARRAY',
    'cities_served' => is_array($userData['cities_served'] ?? null) 
        ? 'ARRAY[' . count($userData['cities_served']) . ']' 
        : 'NOT_ARRAY',
    'delivery_services' => is_array($userData['delivery_services'] ?? null) 
        ? 'ARRAY[' . count($userData['delivery_services']) . ']' 
        : 'NOT_ARRAY',
];

foreach ($displayFields as $field => $value) {
    if (is_string($value) && strlen($value) > 40) {
        $value = substr($value, 0, 37) . '...';
    }
    echo sprintf("%-30s | %-40s\n", $field, $value);
}

echo "\n";

// Test 5: Array contents
echo "TEST 5: ARRAY CONTENTS\n";
echo str_repeat("-", 80) . "\n";

echo "Regions Served:\n";
if (is_array($userData['regions_served'] ?? null)) {
    foreach ($userData['regions_served'] as $idx => $region) {
        echo "  [{$idx}] {$region}\n";
    }
} else {
    echo "  ❌ NOT AN ARRAY\n";
}

echo "\nProvinces Served:\n";
if (is_array($userData['provinces_served'] ?? null)) {
    if (empty($userData['provinces_served'])) {
        echo "  (empty - OK for NCR-only couriers)\n";
    } else {
        foreach ($userData['provinces_served'] as $idx => $province) {
            echo "  [{$idx}] {$province}\n";
        }
    }
} else {
    echo "  ❌ NOT AN ARRAY\n";
}

echo "\nCities Served:\n";
if (is_array($userData['cities_served'] ?? null)) {
    foreach ($userData['cities_served'] as $idx => $city) {
        $commaIndicator = (strpos($city, ',') !== false) ? ' ← COMMA PRESERVED!' : '';
        echo "  [{$idx}] {$city}{$commaIndicator}\n";
    }
} else {
    echo "  ❌ NOT AN ARRAY\n";
}

echo "\nDelivery Services:\n";
if (is_array($userData['delivery_services'] ?? null)) {
    foreach ($userData['delivery_services'] as $idx => $service) {
        echo "  [{$idx}] {$service}\n";
    }
    if (count($userData['delivery_services']) === 3) {
        echo "  ✅ All 3 services saved!\n";
    } else {
        echo "  ⚠️  Expected 3 services, got " . count($userData['delivery_services']) . "\n";
    }
} else {
    echo "  ❌ NOT AN ARRAY\n";
}

echo "\n";
echo str_repeat("=", 80) . "\n";
echo "TEST COMPLETE\n";
echo str_repeat("=", 80) . "\n";
echo "\nCompare the values above with what you typed in the registration form.\n";
echo "ALL values should match EXACTLY (no fake data, no NULL for required fields).\n";
