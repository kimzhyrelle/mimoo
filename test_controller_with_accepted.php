<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Find or create an order with an accepted assignment
$acceptedAssignment = App\Models\ShipmentAssignment::where('assignment_status', 'accepted')->first();

if (!$acceptedAssignment) {
    echo "No accepted assignments found. Creating test data...\n\n";
    
    $order = App\Models\Order::first();
    $user = App\Models\User::where('role', 'courier')->first();
    
    if (!$order || !$user) {
        echo "Need at least one order and one courier user\n";
        exit;
    }
    
    // Clean up any existing assignments for this order
    App\Models\ShipmentAssignment::where('order_id', $order->id)->delete();
    
    // Create new accepted assignment
    $acceptedAssignment = App\Models\ShipmentAssignment::create([
        'order_id' => $order->id,
        'courier_id' => $user->id,
        'assigned_by' => $user->id,
        'assignment_status' => 'accepted',
        'pickup_status' => 'pending_pickup',
        'assigned_at' => now(),
        'acceptance_deadline' => now()->addHours(2),
        'accepted_at' => now(),
    ]);
    
    echo "Created test accepted assignment ID: {$acceptedAssignment->id}\n\n";
}

$order = $acceptedAssignment->order;
$courierId = $acceptedAssignment->courier_id;

echo "=== Testing Order ID: {$order->id} with Courier ID: {$courierId} ===\n\n";

// Check current assignment count
$assignmentCount = App\Models\ShipmentAssignment::where('order_id', $order->id)->count();
echo "Total assignments for this order: {$assignmentCount}\n";

// BEFORE: Show all rows
echo "\n--- All Rows for Order {$order->id} (RAW DATA) ---\n";
$allAssignments = App\Models\ShipmentAssignment::where('order_id', $order->id)->get();
foreach ($allAssignments as $a) {
    $raw = DB::selectOne("SELECT active_order_id FROM shipment_assignments WHERE id = ?", [$a->id]);
    echo "ID: {$a->id}, Courier: {$a->courier_id}, Status: {$a->assignment_status}, Pickup: {$a->pickup_status}, active_order_id: " . ($raw->active_order_id ?? 'NULL') . "\n";
}

// Get the data using the same query as IncomingShipmentsController
echo "\n--- Incoming Shipments Query (ALL statuses) ---\n";
$incomingData = App\Models\ShipmentAssignment::with([
    'order.items.product',
    'order.buyer',
])
->where('order_id', $order->id)
->where('courier_id', $courierId)
->get();

echo "Records returned: " . $incomingData->count() . "\n";
foreach ($incomingData as $data) {
    echo "  - Assignment ID: {$data->id}, Status: {$data->assignment_status}, Pickup: {$data->pickup_status}, Order Total: {$data->order->total}\n";
}

// Get the data using the same query as PickupManagementController
echo "\n--- Pickup Management Query (ONLY accepted) ---\n";
$pickupData = App\Models\ShipmentAssignment::with([
    'order.items',
    'personnel',
])
->where('order_id', $order->id)
->where('courier_id', $courierId)
->where('assignment_status', 'accepted')
->get();

echo "Records returned: " . $pickupData->count() . "\n";
foreach ($pickupData as $data) {
    echo "  - Assignment ID: {$data->id}, Status: {$data->assignment_status}, Pickup: {$data->pickup_status}, Order Total: {$data->order->total}\n";
}

echo "\n=== CONCLUSION ===\n";
echo "Both queries should return the same data for a single accepted assignment.\n";
echo "If the order had a rejected assignment followed by an accepted one,\n";
echo "Incoming Shipments would show both, Pickup Management would show only the accepted one.\n";
