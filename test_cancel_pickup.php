<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$order = App\Models\Order::first();
if (!$order) {
    echo "No orders found\n";
    exit;
}

// Create a cancelled pickup scenario
$courier = App\Models\CourierPersonnel::first();
if (!$courier) {
    echo "No courier found\n";
    exit;
}

$assignment = App\Models\ShipmentAssignment::create([
    'order_id' => $order->id,
    'courier_id' => $courier->id,
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
        'courier_id' => $courier->id,
        'assignment_status' => 'pending',
        'assigned_at' => now(),
    ]);
    echo "SUCCESS: New assignment created (ID: {$newAssignment->id})\n";
    echo "This means cancelled pickup does NOT block new assignments.\n";
} catch (Exception $e) {
    echo "BLOCKED: {$e->getMessage()}\n";
    echo "This means cancelled pickup DOES block new assignments.\n";
}
