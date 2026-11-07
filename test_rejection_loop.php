<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Rejection Loop Test ===\n\n";

// Create test users and couriers
$userA = App\Models\User::create([
    'name' => 'Temp Courier A',
    'email' => 'temp_courier_a_' . time() . '@test.com',
    'password' => bcrypt('password'),
    'role' => 'courier',
]);

$userB = App\Models\User::create([
    'name' => 'Temp Courier B',
    'email' => 'temp_courier_b_' . time() . '@test.com',
    'password' => bcrypt('password'),
    'role' => 'courier',
]);

DB::statement("INSERT INTO courier_personnel (courier_id, name, role, status, created_at, updated_at) VALUES ({$userA->id}, 'Temp Courier A', 'rider', 'active', NOW(), NOW())");
DB::statement("INSERT INTO courier_personnel (courier_id, name, role, status, created_at, updated_at) VALUES ({$userB->id}, 'Temp Courier B', 'rider', 'active', NOW(), NOW())");

$courierA = DB::table('courier_personnel')->where('courier_id', $userA->id)->first();
$courierB = DB::table('courier_personnel')->where('courier_id', $userB->id)->first();

echo "Created temp couriers:\n";
echo "  Courier A: personnel_id={$courierA->id}, user_id={$userA->id}\n";
echo "  Courier B: personnel_id={$courierB->id}, user_id={$userB->id}\n\n";

// Get a test order
$order = App\Models\Order::first();
if (!$order) {
    echo "No orders found\n";
    exit;
}

// Clean up any existing assignments for this order
App\Models\ShipmentAssignment::where('order_id', $order->id)->delete();

echo "Using Order ID: {$order->id}\n\n";

// Step 1: Assign to Courier A
echo "Step 1: Assign to Courier A\n";
$assignmentA = App\Models\ShipmentAssignment::create([
    'order_id' => $order->id,
    'courier_id' => $courierA->id,
    'assigned_by' => $userA->id,
    'assignment_status' => 'pending',
    'pickup_status' => 'pending_pickup',
    'assigned_at' => now(),
    'acceptance_deadline' => now()->addHours(2),
]);
echo "  Created Assignment ID: {$assignmentA->id}\n";
echo "  Status: {$assignmentA->assignment_status}\n";
$rawA1 = DB::selectOne("SELECT active_order_id FROM shipment_assignments WHERE id = ?", [$assignmentA->id]);
echo "  active_order_id: " . ($rawA1->active_order_id ?? 'NULL') . "\n\n";

// Step 2: Courier A rejects
echo "Step 2: Courier A rejects\n";
$assignmentA->update([
    'assignment_status' => 'rejected',
    'rejected_at' => now(),
    'rejection_reason' => 'Test rejection by Courier A',
]);
$assignmentA->refresh();
echo "  Assignment ID: {$assignmentA->id}\n";
echo "  Status: {$assignmentA->assignment_status}\n";
$rawA2 = DB::selectOne("SELECT active_order_id FROM shipment_assignments WHERE id = ?", [$assignmentA->id]);
echo "  active_order_id: " . ($rawA2->active_order_id ?? 'NULL') . "\n\n";

// Step 3: Assign to Courier B
echo "Step 3: Assign to Courier B\n";
try {
    $assignmentB = App\Models\ShipmentAssignment::create([
        'order_id' => $order->id,
        'courier_id' => $courierB->id,
        'assigned_by' => $userB->id,
        'assignment_status' => 'pending',
        'pickup_status' => 'pending_pickup',
        'assigned_at' => now(),
        'acceptance_deadline' => now()->addHours(2),
    ]);
    echo "  SUCCESS: Created Assignment ID: {$assignmentB->id}\n";
    echo "  Status: {$assignmentB->assignment_status}\n";
    $rawB = DB::selectOne("SELECT active_order_id FROM shipment_assignments WHERE id = ?", [$assignmentB->id]);
    echo "  active_order_id: " . ($rawB->active_order_id ?? 'NULL') . "\n\n";
} catch (Exception $e) {
    echo "  FAILED: {$e->getMessage()}\n\n";
}

// Show both rows
echo "=== Final State: All Rows for Order {$order->id} ===\n";
$allAssignments = App\Models\ShipmentAssignment::where('order_id', $order->id)->orderBy('id')->get();
foreach ($allAssignments as $a) {
    $raw = DB::selectOne("SELECT active_order_id FROM shipment_assignments WHERE id = ?", [$a->id]);
    echo "ID: {$a->id}, Courier: {$a->courier_id}, Status: {$a->assignment_status}, Pickup: {$a->pickup_status}, active_order_id: " . ($raw->active_order_id ?? 'NULL') . "\n";
}

// Cleanup
echo "\n=== Cleanup ===\n";
App\Models\ShipmentAssignment::whereIn('courier_id', [$courierA->id, $courierB->id])->delete();
DB::table('courier_personnel')->whereIn('id', [$courierA->id, $courierB->id])->delete();
App\Models\User::whereIn('id', [$userA->id, $userB->id])->delete();
echo "Test data deleted.\n";
