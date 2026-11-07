<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get existing couriers or create minimal test data
$couriers = DB::table('courier_personnel')->limit(2)->get();

if ($couriers->count() < 2) {
    echo "Need at least 2 couriers in database. Creating test couriers via raw SQL...\n";
    DB::statement("INSERT INTO courier_personnel (courier_id, name, role, status, created_at, updated_at) VALUES (999, 'Test Courier 1', 'rider', 'active', NOW(), NOW())");
    DB::statement("INSERT INTO courier_personnel (courier_id, name, role, status, created_at, updated_at) VALUES (998, 'Test Courier 2', 'rider', 'active', NOW(), NOW())");
    $couriers = DB::table('courier_personnel')->where('courier_id', '>=', 998)->get();
}

$courier1 = $couriers[0];
$courier2 = $couriers[1];

echo "Using couriers: {$courier1->id}, {$courier2->id}\n\n";

$order = App\Models\Order::first();
if (!$order) {
    echo "No orders found\n";
    exit;
}

// Create a cancelled pickup scenario
$assignment = App\Models\ShipmentAssignment::create([
    'order_id' => $order->id,
    'courier_id' => $courier1->id,
    'assignment_status' => 'accepted',
    'pickup_status' => 'cancelled',
    'assigned_at' => now(),
]);

echo "Created assignment ID: {$assignment->id}\n";
echo "Order ID: {$order->id}\n";
echo "Status: assignment_status=accepted, pickup_status=cancelled\n\n";

// Try to create a new active assignment
try {
    $newAssignment = App\Models\ShipmentAssignment::create([
        'order_id' => $order->id,
        'courier_id' => $courier2->id,
        'assignment_status' => 'pending',
        'assigned_at' => now(),
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
