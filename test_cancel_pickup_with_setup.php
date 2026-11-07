<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Create test couriers
$courier1 = App\Models\CourierPersonnel::create([
    'user_id' => App\Models\User::where('role', 'courier')->first()->id ?? 1,
    'vehicle_type' => 'motorcycle',
    'vehicle_plate' => 'TEST001',
    'license_number' => 'LIC001',
    'status' => 'active',
]);

$courier2 = App\Models\CourierPersonnel::create([
    'user_id' => App\Models\User::where('role', 'courier')->skip(1)->first()->id ?? 2,
    'vehicle_type' => 'motorcycle',
    'vehicle_plate' => 'TEST002',
    'license_number' => 'LIC002',
    'status' => 'active',
]);

echo "Created test couriers: {$courier1->id}, {$courier2->id}\n\n";

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
