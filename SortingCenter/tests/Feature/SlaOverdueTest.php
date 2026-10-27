<?php

namespace Tests\Feature;

use App\Http\Resources\ParcelResource;
use App\Models\Area;
use App\Models\Parcel;
use App\Models\Rider;
use App\Services\MetricsService;
use App\Services\ParcelStatusService;
use Database\Seeders\SortingCenterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SlaOverdueTest extends TestCase
{
    use RefreshDatabase;

    protected ParcelStatusService $statusService;

    protected MetricsService $metrics;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SortingCenterSeeder::class);
        $this->statusService = app(ParcelStatusService::class);
        $this->metrics = app(MetricsService::class);
    }

    public function test_sla_due_at_automatically_set_on_transition(): void
    {
        $area = Area::first();
        $parcel = Parcel::create([
            'tracking_no' => '#SLA-TEST-001',
            'status' => 'dropped_off',
            'area_id' => $area->id,
            'address' => 'Brgy. Poblacion I',
            'seller_name' => 'Seller Test',
            'received_at' => now()->subMinutes(10),
            'sla_due_at' => null,
            'scan_count' => 1,
        ]);

        $this->assertNull($parcel->sla_due_at);

        $res = $this->statusService->transition($parcel, 'awaiting_sort');
        $this->assertTrue($res['ok']);

        $refreshed = $parcel->fresh();
        $this->assertNotNull($refreshed->sla_due_at);

        $expectedDue = $refreshed->received_at->copy()->addHours(config('sorting.sla_hours', 24));
        $this->assertSame($expectedDue->timestamp, $refreshed->sla_due_at->timestamp);
    }

    public function test_is_overdue_logic(): void
    {
        $area = Area::first();

        // 1. Overdue parcel: sla_due_at is past and status is sorted
        $overdueParcel = Parcel::create([
            'tracking_no' => '#SLA-OVERDUE-1',
            'status' => 'sorted',
            'area_id' => $area->id,
            'address' => 'Test Address',
            'seller_name' => 'Test Seller',
            'received_at' => now()->subHours(26),
            'sla_due_at' => now()->subHours(2),
            'scan_count' => 2,
        ]);
        $this->assertTrue($overdueParcel->isOverdue());

        // 2. Not overdue: sla_due_at is in future
        $onTimeParcel = Parcel::create([
            'tracking_no' => '#SLA-ONTIME-1',
            'status' => 'sorted',
            'area_id' => $area->id,
            'address' => 'Test Address',
            'seller_name' => 'Test Seller',
            'received_at' => now()->subHours(2),
            'sla_due_at' => now()->addHours(22),
            'scan_count' => 2,
        ]);
        $this->assertFalse($onTimeParcel->isOverdue());

        // 3. Delivered parcel is NEVER overdue even if sla_due_at is past
        $deliveredParcel = Parcel::create([
            'tracking_no' => '#SLA-DELIVERED-1',
            'status' => 'delivered',
            'area_id' => $area->id,
            'address' => 'Test Address',
            'seller_name' => 'Test Seller',
            'received_at' => now()->subHours(30),
            'sla_due_at' => now()->subHours(6),
            'delivered_at' => now()->subMinutes(10),
            'scan_count' => 4,
        ]);
        $this->assertFalse($deliveredParcel->isOverdue());

        // 4. Returned parcel is NEVER overdue
        $returnedParcel = Parcel::create([
            'tracking_no' => '#SLA-RETURNED-1',
            'status' => 'returned',
            'area_id' => $area->id,
            'address' => 'Test Address',
            'seller_name' => 'Test Seller',
            'received_at' => now()->subHours(30),
            'sla_due_at' => now()->subHours(6),
            'scan_count' => 3,
        ]);
        $this->assertFalse($returnedParcel->isOverdue());

        // 5. Failed parcel without sla_due_at is overdue
        $failedParcel = Parcel::create([
            'tracking_no' => '#SLA-FAILED-1',
            'status' => 'failed',
            'area_id' => $area->id,
            'address' => 'Test Address',
            'seller_name' => 'Test Seller',
            'received_at' => now()->subHours(5),
            'sla_due_at' => null,
            'scan_count' => 3,
        ]);
        $this->assertTrue($failedParcel->isOverdue());
    }

    public function test_overdue_minutes_and_label_formatting(): void
    {
        $area = Area::first();

        // 2 hours and 14 minutes overdue
        $parcel1 = Parcel::create([
            'tracking_no' => '#SLA-LABEL-1',
            'status' => 'out_for_delivery',
            'area_id' => $area->id,
            'address' => 'Test Address',
            'seller_name' => 'Test Seller',
            'received_at' => now()->subHours(27),
            'sla_due_at' => now()->subHours(2)->subMinutes(14),
            'scan_count' => 3,
        ]);
        $this->assertEqualsWithDelta(134, $parcel1->overdueMinutes(), 2);
        $this->assertSame('2h 14m over', $parcel1->overdueLabel());

        // 45 minutes overdue
        $parcel2 = Parcel::create([
            'tracking_no' => '#SLA-LABEL-2',
            'status' => 'assigned',
            'area_id' => $area->id,
            'address' => 'Test Address',
            'seller_name' => 'Test Seller',
            'received_at' => now()->subHours(25),
            'sla_due_at' => now()->subMinutes(45),
            'scan_count' => 3,
        ]);
        $this->assertEqualsWithDelta(45, $parcel2->overdueMinutes(), 2);
        $this->assertSame('45m over', $parcel2->overdueLabel());

        // On time parcel produces empty label and 0 minutes
        $parcel3 = Parcel::create([
            'tracking_no' => '#SLA-LABEL-3',
            'status' => 'assigned',
            'area_id' => $area->id,
            'address' => 'Test Address',
            'seller_name' => 'Test Seller',
            'received_at' => now()->subHours(1),
            'sla_due_at' => now()->addHours(23),
            'scan_count' => 3,
        ]);
        $this->assertSame(0, $parcel3->overdueMinutes());
        $this->assertSame('', $parcel3->overdueLabel());
    }

    public function test_scope_overdue_query(): void
    {
        $area = Area::first();

        $overdueCountBefore = Parcel::overdue()->count();

        // Create an overdue parcel
        Parcel::create([
            'tracking_no' => '#SLA-SCOPE-1',
            'status' => 'assigned',
            'area_id' => $area->id,
            'address' => 'Test Address',
            'seller_name' => 'Test Seller',
            'received_at' => now()->subHours(26),
            'sla_due_at' => now()->subHours(2),
            'scan_count' => 3,
        ]);

        $this->assertSame($overdueCountBefore + 1, Parcel::overdue()->count());
    }

    public function test_scan_count_and_custody_events_never_show_one_fourth_for_in_flight_or_delivered(): void
    {
        $area = Area::first();
        $rider = Rider::where('is_active', true)->firstOrFail();

        // 1. Parcel with raw scan_count = 1 in database but status = out_for_delivery
        $inFlightParcel = Parcel::create([
            'tracking_no' => '#CUSTODY-OUT-1',
            'status' => 'out_for_delivery',
            'area_id' => $area->id,
            'rider_id' => $rider->id,
            'address' => 'Test Address',
            'seller_name' => 'Test Seller',
            'received_at' => now()->subHours(2),
            'assigned_at' => now()->subMinutes(30),
            'sorted_at' => null, // skipped / unrecorded intermediate scan
            'scan_count' => 1,
        ]);

        // effectiveScanCount should guarantee minimum 3
        $this->assertGreaterThanOrEqual(3, $inFlightParcel->effectiveScanCount());
        $this->assertSame(4, $inFlightParcel->expectedScans());

        // custodyEvents() must show all prior steps and flag missing ones as missed
        $events = $inFlightParcel->custodyEvents();
        $this->assertGreaterThanOrEqual(3, $events->count());

        $sortedEvent = $events->firstWhere('title', 'Sorted');
        $this->assertNotNull($sortedEvent);
        $this->assertTrue($sortedEvent['missed'], 'Skipped intermediate sorted scan should be flagged as missed');

        // 2. Delivered parcel
        $deliveredParcel = Parcel::create([
            'tracking_no' => '#CUSTODY-DEL-1',
            'status' => 'delivered',
            'area_id' => $area->id,
            'rider_id' => $rider->id,
            'address' => 'Test Address',
            'seller_name' => 'Test Seller',
            'received_at' => now()->subHours(3),
            'delivered_at' => now()->subMinutes(5),
            'sorted_at' => null,
            'assigned_at' => null,
            'scan_count' => 1,
        ]);

        $this->assertSame(4, $deliveredParcel->effectiveScanCount());
        $delEvents = $deliveredParcel->custodyEvents();
        $this->assertSame(4, $delEvents->count());
    }

    public function test_scan_timestamp_earlier_than_received_throws_validation_exception(): void
    {
        $area = Area::first();
        $parcel = Parcel::create([
            'tracking_no' => '#SCAN-VAL-1',
            'status' => 'dropped_off',
            'area_id' => $area->id,
            'address' => 'Test Address',
            'seller_name' => 'Test Seller',
            'received_at' => now()->subHours(2),
            'scan_count' => 1,
        ]);

        $this->expectException(ValidationException::class);
        $parcel->validateScanTimestamp(now()->subHours(5));
    }

    public function test_metrics_hourly_processed_returns_24_zero_filled_points(): void
    {
        $hourly = $this->metrics->hourlyProcessed();

        $this->assertCount(24, $hourly);
        for ($h = 0; $h < 24; $h++) {
            $this->assertSame($h, $hourly[$h]['hour']);
            $this->assertArrayHasKey('count', $hourly[$h]);
            $this->assertIsInt($hourly[$h]['count']);
            $this->assertGreaterThanOrEqual(0, $hourly[$h]['count']);
        }
    }

    public function test_metrics_status_breakdown_list_and_overdue_count(): void
    {
        $breakdown = $this->metrics->statusBreakdownList();

        $this->assertIsArray($breakdown);
        $this->assertCount(4, $breakdown);

        $statuses = array_column($breakdown, 'status');
        $this->assertContains('Delivered', $statuses);
        $this->assertContains('Out for Delivery', $statuses);
        $this->assertContains('In Transit', $statuses);
        $this->assertContains('To Sort', $statuses);

        foreach ($breakdown as $item) {
            $this->assertArrayHasKey('status', $item);
            $this->assertArrayHasKey('count', $item);
            $this->assertIsInt($item['count']);
        }

        $this->assertSame($this->metrics->overdueCountValue(), Parcel::overdue()->count());
    }

    public function test_parcel_resource_includes_sla_and_scan_fields(): void
    {
        $parcel = Parcel::where('status', 'failed')->firstOrFail();
        $resource = (new ParcelResource($parcel))->toArray(Request::create('/api/parcels'));

        $this->assertArrayHasKey('sla_due_at', $resource);
        $this->assertArrayHasKey('is_overdue', $resource);
        $this->assertArrayHasKey('overdue_minutes', $resource);
        $this->assertArrayHasKey('overdue_label', $resource);
        $this->assertArrayHasKey('scan_count', $resource);
        $this->assertArrayHasKey('expected_scans', $resource);

        $this->assertTrue($resource['is_overdue']);
        $this->assertGreaterThanOrEqual(3, $resource['scan_count']);
        $this->assertSame(4, $resource['expected_scans']);
    }
}
