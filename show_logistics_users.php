<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== ALL LOGISTICS USERS ===\n\n";
echo sprintf("%-4s | %-35s | %-15s | %-10s | %-30s\n", "ID", "Email", "Role", "Status", "Company Name");
echo str_repeat("-", 110) . "\n";

$users = DB::table('users')->where('account_type', 'logistics')->get(['id', 'email', 'role', 'account_type', 'company_name', 'status']);

foreach ($users as $u) {
    echo sprintf("%-4d | %-35s | %-15s | %-10s | %-30s\n", 
        $u->id, 
        $u->email, 
        $u->role ?? 'NULL', 
        $u->status, 
        $u->company_name ?? 'NULL'
    );
}

echo "\nTotal: " . $users->count() . " users\n";
echo "Couriers (role=courier): " . $users->where('role', 'courier')->count() . "\n";
echo "Staff (role=logistics_staff): " . $users->where('role', 'logistics_staff')->count() . "\n";
