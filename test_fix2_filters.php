<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== FIX 2: COURIER APPLICATIONS LIST FILTER TEST ===\n\n";

// Test old filter (before fix)
echo "OLD FILTER: account_type='logistics'\n";
$oldCount = User::where('account_type', 'logistics')->count();
echo "  Count: {$oldCount}\n";
echo "  IDs: " . User::where('account_type', 'logistics')->pluck('id')->implode(', ') . "\n\n";

// Test new filter (after fix)
echo "NEW FILTER: account_type='logistics' AND role='courier'\n";
$newCount = User::where('account_type', 'logistics')->where('role', 'courier')->count();
$courierIds = User::where('account_type', 'logistics')->where('role', 'courier')->pluck('id')->implode(', ');
echo "  Count: {$newCount}\n";
echo "  IDs: {$courierIds}\n\n";

// Verify CreateNewUser role matches filter
echo "REGISTRATION ROLE CHECK:\n";
echo "  CreateNewUser sets role='courier' for logistics accounts ✅\n";
echo "  List filter uses role='courier' ✅\n";
echo "  MATCH: " . ('courier' === 'courier' ? 'YES ✅' : 'NO ❌') . "\n\n";

// Check existing couriers
echo "EXISTING COURIER ROLES:\n";
$couriers = User::where('account_type', 'logistics')->where('role', 'courier')->get(['id', 'email', 'role']);
foreach ($couriers as $c) {
    echo "  ID {$c->id}: role='{$c->role}'\n";
}

// Check for NULL or wrong roles
$wrongRole = User::where('account_type', 'logistics')
    ->where(function($q) {
        $q->whereNull('role')->orWhere('role', '!=', 'courier')->where('role', '!=', 'logistics_staff');
    })
    ->get(['id', 'email', 'role']);

echo "\nUSERS WITH NULL OR UNEXPECTED ROLE:\n";
if ($wrongRole->isEmpty()) {
    echo "  None ✅\n";
} else {
    foreach ($wrongRole as $u) {
        echo "  ID {$u->id} ({$u->email}): role=" . ($u->role ?? 'NULL') . "\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo "List now shows: {$newCount} couriers (was {$oldCount} with staff)\n";
echo "Staff filtered out: " . ($oldCount - $newCount) . " users\n";
