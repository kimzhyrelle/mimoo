<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$u = \App\Models\User::find(12);

echo "User ID 12 Details:\n";
echo str_repeat("=", 80) . "\n";
echo "Name: {$u->name}\n";
echo "Email: {$u->email}\n";
echo "Company name: " . ($u->company_name ?? 'NULL') . "\n";
echo "Business name: " . ($u->business_name ?? 'NULL') . "\n";
echo "Contact first name: " . ($u->contact_first_name ?? 'NULL') . "\n";
echo "Contact last name: " . ($u->contact_last_name ?? 'NULL') . "\n";
echo "SEC registration: " . ($u->sec_registration_no ?? 'NULL') . "\n";
