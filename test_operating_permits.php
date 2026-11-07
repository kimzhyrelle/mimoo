<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== OPERATING_PERMITS vs BIR_2303 FOR LOGISTICS USERS ===\n\n";

$logistics = DB::table('users')
    ->where('account_type', 'logistics')
    ->get(['id', 'email', 'bir_2303', 'operating_permits']);

foreach ($logistics as $user) {
    echo "ID {$user->id} ({$user->email}):\n";
    echo "  bir_2303: " . ($user->bir_2303 ?? 'NULL') . "\n";
    echo "  operating_permits: " . ($user->operating_permits ?? 'NULL') . "\n\n";
}

echo "Total logistics users: " . $logistics->count() . "\n";
