<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Create test users and couriers
$user1 = App\Models\User::create([
    'name' => 'Test Courier User 1',
    'email' => 'testcourier1_' . time() . '@test.com',
    'password' => bcrypt('password'),
    'role' => 'courier',
]);

$user2 = App\Models\User::create([
    'name' => 'Test Courier User 2',
    'email' => 'testcourier2_' . time() . '@test.com',
    'password' => bcrypt('password'),
    'role' => 'courier',
]);

DB::statement("INSERT INTO courier_personnel (courier_id, name, role, status, created_at, updated_at) VALUES ({$user1->id}, 'Test Courier 1', 'rider', 'active', NOW(), NOW())");
DB::statement("INSERT INTO courier_personnel (courier_id, name, role, status, created_at, updated_at) VALUES ({$user2->id}, 'Test Courier 2', 'rider', 'active', NOW(), NOW())");

$courier1 = DB::table('courier_personnel')->where('courier_id', $user1->id)->first();
$courier2 = DB::table('courier_personnel')->where('courier_id', $user2->id)->first();

echo "Created test couriers: {$courier1->id} (user {$user1->id}), {$courier2->id} (user {$user2->id})\n\n";

$order = App\Models\Order::first();
if (!$order) {
    echo "No orders found\n";
    exit;
}

// Create a cancelled pickup scenario
$assignment = App\Models\ShipmentAssignment::create([
    'order_id' => $order->id,
    'courier_id' => $courier1->id,
    'assigned_by' => $user1->id,
    'assignment_status' => 'accepted',
    'pickup_status' => 'cancelled',
    'assigned_at' => now(),
    'acceptance_deadline' => now()->addHours(2),
]);

echo "Created assignment ID: {$assignment->id}\n";
echo "Order ID: {$order->id}\n";
echo "Status: assignment_status=accepted, pickup_status=cancelled\n\n";

// Try to create a new active assignment
try {
    $newAssignment = App\Models\ShipmentAssignment::create([
        'order_id' => $order->id,
        'courier_id' => $courier2->id,
        'assigned_by' => $user2->id,
        'assignment_status' => 'pending',
        'assigned_at' => now(),
        'acceptance_deadline' => now()->addHours(2),
    ]);
    echo "SUCCESS: New assignment created (ID: {$newAssignment->id})\n";
    echo "This means cancelled pickup does NOT block new assignments.\n\n";
    
    echo "Current assignments for order {$order->id}:\n";
    $assignments = App\Models\ShipmentAssignment::where('order_id', $order->id)->get();
    foreach ($assignments as $a) {
        echo "  - ID {$a->id}: courier={$a->courier_id}, status={$a->assignment_status}, pickup={$a->pickup_status}\n";
    }
} catch (Exception $e) {
    echo "BLOCKED: {$e->getMessage()}\n";
    echo "This means cancelled pickup DOES block new assignments.\n";
}

// Cleanup
echo "\n\nCleaning up test data...\n";
App\Models\ShipmentAssignment::whereIn('courier_id', [$courier1->id, $courier2->id])->delete();
DB::table('courier_personnel')->whereIn('id', [$courier1->id, $courier2->id])->delete();
App\Models\User::whereIn('id', [$user1->id, $user2->id])->delete();
echo "Done.\n";
