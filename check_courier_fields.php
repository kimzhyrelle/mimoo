<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "Checking courier registration fields in users table:\n";
echo str_repeat("=", 70) . "\n";

$fieldsToCheck = [
    'business_email',
    'username',
    'company_contact_number',
    'rep_contact_number',
    'business_address',
    'rep_full_name',
    'regions_served',
    'provinces_served',
    'cities_served',
    'operating_permits',
];

foreach ($fieldsToCheck as $fieldName) {
    $columns = Schema::getColumns('users');
    $col = collect($columns)->firstWhere('name', $fieldName);
    
    if ($col) {
        $nullable = $col['nullable'] ? 'YES' : 'NO';
        $type = $col['type_name'];
        echo "✅ {$fieldName} - Type: {$type}, Null: {$nullable}\n";
    } else {
        echo "❌ {$fieldName} - MISSING!\n";
    }
}

echo str_repeat("=", 70) . "\n";
echo "Schema verification complete.\n";
