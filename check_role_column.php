<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "=== ROLE COLUMN CHECK ===\n\n";

$columns = Schema::getColumnListing('users');
$roleExists = in_array('role', $columns);

echo "role column: " . ($roleExists ? "EXISTS" : "MISSING") . "\n\n";

if ($roleExists) {
    echo "=== LOGISTICS USERS BY ROLE ===\n\n";
    $logistics = DB::table('users')
        ->where('account_type', 'logistics')
        ->get(['id', 'email', 'role', 'company_name']);
    
    foreach ($logistics as $user) {
        echo "ID {$user->id}: role='{$user->role}', company_name=" . ($user->company_name ?? 'NULL') . "\n";
    }
    
    echo "\n=== COURIER APPLICATIONS LIST FILTER ===\n";
    echo "Current filter: account_type='logistics'\n";
    echo "This includes:\n";
    
    $couriers = DB::table('users')->where('account_type', 'logistics')->where('role', 'courier')->count();
    $staff = DB::table('users')->where('account_type', 'logistics')->where('role', 'logistics_staff')->count();
    $other = DB::table('users')->where('account_type', 'logistics')->whereNotIn('role', ['courier', 'logistics_staff'])->count();
    
    echo "  - role='courier': {$couriers} users\n";
    echo "  - role='logistics_staff': {$staff} users\n";
    echo "  - other roles: {$other} users\n";
    echo "\n";
    echo "RESULT: Logistics staff " . ($staff > 0 ? "WOULD" : "would NOT") . " appear in the list.\n";
}
