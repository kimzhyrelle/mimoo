<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$total = \App\Models\User::count();
$latest = \App\Models\User::orderBy('id', 'desc')->first();

echo "Total users: $total\n";
echo "Latest user:\n";
echo "  ID: {$latest->id}\n";
echo "  Email: {$latest->email}\n";
echo "  Name: {$latest->name}\n";
echo "  Account Type: {$latest->account_type}\n";
echo "  Company Name: " . ($latest->company_name ?? 'NULL') . "\n";
echo "  Status: {$latest->status}\n";
echo "  Created: {$latest->created_at}\n";
