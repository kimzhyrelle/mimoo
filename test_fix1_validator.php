<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;

echo "=== FIX 1: VALIDATOR TEST ===\n\n";

// Create a fake file
$fakeFile = UploadedFile::fake()->create('test.pdf', 100);

// Test 1: Logistics with only valid_id (NO id_document)
echo "Test 1: Logistics account with valid_id only (NO id_document)\n";
$data1 = [
    'account_type' => 'logistics',
    'valid_id' => $fakeFile,
    // NO id_document
];

$rules = [
    'account_type' => ['required'],
    'id_document' => [($data1['account_type'] ?? '') === 'logistics' ? 'nullable' : 'required', 'file'],
    'valid_id' => ['required_if:account_type,logistics', 'nullable', 'file'],
];

$validator1 = Validator::make($data1, $rules);

if ($validator1->fails()) {
    echo "  RESULT: FAILED\n";
    foreach ($validator1->errors()->all() as $error) {
        echo "    - {$error}\n";
    }
} else {
    echo "  RESULT: PASSED ✅\n";
}

echo "\n";

// Test 2: Buyer with id_document (required)
echo "Test 2: Buyer account with id_document (required)\n";
$data2 = [
    'account_type' => 'buyer',
    'id_document' => $fakeFile,
];

$rules2 = [
    'account_type' => ['required'],
    'id_document' => [($data2['account_type'] ?? '') === 'logistics' ? 'nullable' : 'required', 'file'],
];

$validator2 = Validator::make($data2, $rules2);

if ($validator2->fails()) {
    echo "  RESULT: FAILED\n";
    foreach ($validator2->errors()->all() as $error) {
        echo "    - {$error}\n";
    }
} else {
    echo "  RESULT: PASSED ✅\n";
}

echo "\n";

// Test 3: Buyer WITHOUT id_document (should fail)
echo "Test 3: Buyer account WITHOUT id_document (should fail)\n";
$data3 = [
    'account_type' => 'buyer',
    // NO id_document
];

$rules3 = [
    'account_type' => ['required'],
    'id_document' => [($data3['account_type'] ?? '') === 'logistics' ? 'nullable' : 'required', 'file'],
];

$validator3 = Validator::make($data3, $rules3);

if ($validator3->fails()) {
    echo "  RESULT: FAILED (as expected) ✅\n";
    foreach ($validator3->errors()->all() as $error) {
        echo "    - {$error}\n";
    }
} else {
    echo "  RESULT: PASSED (unexpected!)\n";
}

echo "\n=== CONCLUSION ===\n";
echo "Logistics with only valid_id: " . ($validator1->passes() ? "PASSES ✅" : "BLOCKED ❌") . "\n";
echo "Buyer without id_document: " . ($validator3->fails() ? "FAILS ✅" : "PASSES ❌") . "\n";
