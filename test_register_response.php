<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

echo "=== TEST 1: Duplicate Email (Validation Error) ===\n";

// Find an existing email
$existingEmail = DB::table('users')->where('account_type', 'logistics')->first()->email ?? 'test@test.com';
echo "Using existing email: {$existingEmail}\n\n";

$request = Request::create('/register', 'POST', [
    'email' => $existingEmail,
    'password' => 'TestPass123!',
    'password_confirmation' => 'TestPass123!',
    'first_name' => 'Test',
    'last_name' => 'User',
    'contact_number' => '09123456789',
    'account_type' => 'buyer',
    'sex' => 'male',
    'birthday' => '2000-01-01',
    'region' => 'NCR',
    'municipality_city' => 'Manila',
    'barangay' => 'Test',
    'street_address' => '123 Test St',
    'terms_accepted' => '1',
]);
$request->headers->set('Accept', 'application/json');

try {
    $response = $kernel->handle($request);
    echo "Status: {$response->getStatusCode()}\n";
    echo "Content-Type: {$response->headers->get('Content-Type')}\n";
    echo "Body:\n";
    echo $response->getContent() . "\n";
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

$kernel->terminate($request, $response ?? null);
