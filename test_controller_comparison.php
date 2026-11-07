<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get first order with assignment
$order = App\Models\Order::whereHas('shipmentAssignments')->first();

if (!$order) {
    echo "No orders with assignments found\n";
    exit;
}

echo "=== Testing Order ID: {$order->id} ===\n\n";

// Check current assignment count
$assignmentCount = App\Models\ShipmentAssignment::where('order_id', $order->id)->count();
echo "Total assignments for this order: {$assignmentCount}\n";

// Get the data using the same query as IncomingShipmentsController
echo "\n--- Incoming Shipments Query ---\n";
$incomingData = App\Models\ShipmentAssignment::with([
    'order.items.product',
    'order.buyer',
])
->where('order_id', $order->id)
->where('courier_id', App\Models\ShipmentAssignment::where('order_id', $order->id)->first()->courier_id ?? 1)
->first();

if ($incomingData) {
    echo "Assignment ID: {$incomingData->id}\n";
    echo "Order ID: {$incomingData->order_id}\n";
    echo "Courier ID: {$incomingData->courier_id}\n";
    echo "Assignment Status: {$incomingData->assignment_status}\n";
    echo "Pickup Status: {$incomingData->pickup_status}\n";
    echo "Order Total: {$incomingData->order->total}\n";
} else {
    echo "No data returned\n";
}

// Get the data using the same query as PickupManagementController
echo "\n--- Pickup Management Query ---\n";
$pickupData = App\Models\ShipmentAssignment::with([
    'order.items',
    'personnel',
])
->where('order_id', $order->id)
->where('courier_id', App\Models\ShipmentAssignment::where('order_id', $order->id)->first()->courier_id ?? 1)
->where('assignment_status', 'accepted')
->first();

if ($pickupData) {
    echo "Assignment ID: {$pickupData->id}\n";
    echo "Order ID: {$pickupData->order_id}\n";
    echo "Courier ID: {$pickupData->courier_id}\n";
    echo "Assignment Status: {$pickupData->assignment_status}\n";
    echo "Pickup Status: {$pickupData->pickup_status}\n";
    echo "Order Total: {$pickupData->order->total}\n";
} else {
    echo "No data returned (this is expected if assignment_status != 'accepted')\n";
}

// Show all rows for this order
echo "\n--- All Rows for Order {$order->id} ---\n";
$allAssignments = App\Models\ShipmentAssignment::where('order_id', $order->id)->get();
foreach ($allAssignments as $a) {
    echo "ID: {$a->id}, Courier: {$a->courier_id}, Status: {$a->assignment_status}, Pickup: {$a->pickup_status}, active_order_id: ";
    try {
        $raw = DB::selectOne("SELECT active_order_id FROM shipment_assignments WHERE id = ?", [$a->id]);
        echo $raw->active_order_id ?? 'NULL';
    } catch (Exception $e) {
        echo "error";
    }
    echo "\n";
}
