<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== FIX 3: EMPTY ARRAY CAST TEST ===\n\n";

// Create a test record with empty provinces_served
echo "Creating test user with provinces_served = []\n";
$testUser = User::create([
    'name' => 'Test NCR Courier',
    'first_name' => 'Test',
    'last_name' => 'NCR',
    'email' => 'test_ncr_' . time() . '@test.com',
    'password' => bcrypt('password'),
    'contact_number' => '+639' . rand(100000000, 999999999),
    'account_type' => 'logistics',
    'role' => 'courier',
    'company_name' => 'NCR Test Courier',
    'regions_served' => ['National Capital Region (NCR)'],
    'provinces_served' => [], // EMPTY ARRAY
    'cities_served' => ['Manila', 'Quezon City'],
    'delivery_services' => ['Same-Day'],
    'status' => 'pending',
]);

echo "User created: ID {$testUser->id}\n\n";

// Check raw DB value
$raw = DB::table('users')->where('id', $testUser->id)->first();
echo "RAW DB VALUE:\n";
echo "  provinces_served: " . ($raw->provinces_served ?? 'NULL') . "\n";
echo "  Type: " . gettype($raw->provinces_served) . "\n\n";

// Check model cast
$model = User::find($testUser->id);
echo "MODEL CAST VALUE:\n";
echo "  provinces_served: " . json_encode($model->provinces_served) . "\n";
echo "  Type: " . gettype($model->provinces_served) . "\n";
echo "  Is array: " . (is_array($model->provinces_served) ? 'YES' : 'NO') . "\n";
echo "  Count: " . (is_array($model->provinces_served) ? count($model->provinces_served) : 'N/A') . "\n\n";

echo "TEST RESULT: " . (is_array($model->provinces_served) && count($model->provinces_served) === 0 ? "PASSED ✅" : "FAILED ❌") . "\n";

// Clean up
$testUser->delete();
echo "\nTest user deleted.\n";
