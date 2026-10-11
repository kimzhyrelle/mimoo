<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== BUYER ACCOUNTS ===\n\n";

$buyers = \App\Models\User::where('account_type', 'buyer')->get();

if ($buyers->isEmpty()) {
    echo "No buyer accounts found.\n\n";
    echo "To create a buyer account:\n";
    echo "1. Go to: http://localhost:8000/register\n";
    echo "2. Fill in the registration form\n";
    echo "3. Select 'Buyer' as account type\n";
} else {
    foreach ($buyers as $buyer) {
        echo "ID: {$buyer->id}\n";
        echo "Name: {$buyer->name}\n";
        echo "Email: {$buyer->email}\n";
        echo "Status: {$buyer->status}\n";
        echo "Created: {$buyer->created_at}\n";
        echo "---\n";
    }
    echo "\nTotal: {$buyers->count()} buyer(s)\n";
}

echo "\n=== ALL ACCOUNTS ===\n\n";
$allUsers = \App\Models\User::select('id', 'name', 'email', 'account_type', 'status')->get();

foreach ($allUsers as $user) {
    echo "{$user->id}. {$user->name} ({$user->email}) - {$user->account_type} [{$user->status}]\n";
}

echo "\nTotal users: {$allUsers->count()}\n";
