<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;

echo "=== OLD COURIER RECORDS (IDs 10-16) ===\n\n";

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

for ($id = 10; $id <= 16; $id++) {
    $user = User::find($id);
    
    if (!$user || $user->account_type !== 'logistics') {
        echo "ID {$id}: Not found or not logistics\n\n";
        continue;
    }
    
    echo "ID {$id} ({$user->email}):\n";
    
    foreach ($newFields as $field) {
        $value = $user->$field;
        
        if (is_null($value)) {
            echo "  {$field}: NULL\n";
        } elseif (is_array($value)) {
            echo "  {$field}: ARRAY[" . count($value) . "] = " . json_encode($value) . "\n";
        } elseif (is_string($value) && strlen($value) > 50) {
            echo "  {$field}: " . substr($value, 0, 50) . "...\n";
        } else {
            echo "  {$field}: {$value}\n";
        }
    }
    
    // Check old fields
    echo "  [OLD] service_areas: ";
    if (is_null($user->service_areas)) {
        echo "NULL\n";
    } elseif (is_array($user->service_areas)) {
        echo "ARRAY[" . count($user->service_areas) . "]\n";
    } else {
        echo "UNKNOWN_TYPE\n";
    }
    
    echo "\n";
}

echo "=== NULL FIELD SAFETY CHECK ===\n";
echo "Checking if any code can crash on NULL values...\n\n";

// Simulate what View Details route does
$testUser = User::find(10);
if ($testUser) {
    echo "Testing array access on NULL fields:\n";
    
    // This is what could crash: treating NULL as array
    $regions = $testUser->regions_served ?? [];
    echo "  regions_served ?? [] = " . (is_array($regions) ? 'SAFE' : 'CRASH') . "\n";
    
    $provinces = $testUser->provinces_served ?? [];
    echo "  provinces_served ?? [] = " . (is_array($provinces) ? 'SAFE' : 'CRASH') . "\n";
    
    $cities = $testUser->cities_served ?? [];
    echo "  cities_served ?? [] = " . (is_array($cities) ? 'SAFE' : 'CRASH') . "\n";
    
    $services = $testUser->delivery_services ?? [];
    echo "  delivery_services ?? [] = " . (is_array($services) ? 'SAFE' : 'CRASH') . "\n";
}
