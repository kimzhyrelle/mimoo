<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Parcel;
use App\Models\Rider;
use App\Services\AssignmentService;
use App\Services\MetricsService;
use Database\Seeders\SortingCenterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected AssignmentService $assignmentService;

    protected MetricsService $metrics;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SortingCenterSeeder::class);
        $this->assignmentService = app(AssignmentService::class);
        $this->metrics = app(MetricsService::class);
    }

    public function test_inactive_or_off_duty_rider_cannot_be_assigned(): void
    {
        $inactiveRider = Rider::where('is_active', false)->firstOrFail();
        $parcel = Parcel::where('status', 'sorted')->whereNotNull('area_id')->firstOrFail();

        // 1. Service check throws 422 ValidationException
        try {
            $this->assignmentService->assignParcel($parcel, $inactiveRider);
            $this->fail('Should fail when assigning inactive rider');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('rider_id', $e->errors());
        }

        // 2. Off-duty rider check
        $offDutyRider = Rider::where('is_active', true)->whereNull('checked_in_at')->firstOrFail();
        try {
            $this->assignmentService->assignParcel($parcel, $offDutyRider);
            $this->fail('Should fail when assigning off-duty rider');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('rider_id', $e->errors());
        }
    }

    public function test_parcel_without_barangay_cannot_be_assigned(): void
    {
        $parcel = Parcel::where('status', 'awaiting_sort')->firstOrFail();
        $parcel->update(['area_id' => null]);
        $activeRider = Rider::where('is_active', true)->whereNotNull('checked_in_at')->firstOrFail();

        $this->expectException(ValidationException::class);
        $this->assignmentService->assignParcel($parcel, $activeRider);
    }

    public function test_unassigned_clusters_are_detected_and_banner_is_computed(): void
    {
        $unassigned = $this->assignmentService->getUnassignedClusters();
        $this->assertNotEmpty($unassigned);
        $this->assertTrue($unassigned->contains('code', 'R10'), 'Lumban (R10) must be detected as unassigned cluster');

        // Check command executes successfully
        $this->artisan('sorting:check-unassigned-clusters')->assertSuccessful();

        // Check Sort Parcels page computes real assignedClusters
        $response = $this->get(route('parcels.sort'));
        $response->assertOk();
        $response->assertViewHas('assignedClusters');
        $assignedClusters = $response->viewData('assignedClusters');
        $clusters = $response->viewData('clusters');

        $this->assertSame(
            $clusters->filter(fn ($c) => $c->area->rider && $c->area->rider->isActive())->count(),
            $assignedClusters
        );
    }

    public function test_auto_cluster_returns_structured_result_with_manual_ids(): void
    {
        // Create an unmatchable parcel
        $unknown = Parcel::create([
            'tracking_no' => '#9999',
            'address' => 'Unknown Sector 99, NonExistent Town',
            'status' => 'awaiting_sort',
        ]);

        $result = $this->assignmentService->autoCluster();

        $this->assertTrue($result['ok']);
        $this->assertIsArray($result['sorted']);
        $this->assertIsArray($result['needs_manual_barangay']);
        $this->assertContains($unknown->id, $result['needs_manual_barangay']);
        $this->assertNull($unknown->fresh()->area_id);
    }

    public function test_rider_with_open_parcels_blocks_unassign_with_409(): void
    {
        // Rider 1 (J. Ramos) has open parcels (assigned / out_for_delivery)
        $rider = Rider::where('initials', 'R1')->firstOrFail();
        $openCount = $rider->parcels()->whereIn('status', ['assigned', 'out_for_delivery'])->count();
        $this->assertGreaterThan(0, $openCount);

        // Try unassigning area without reassign_to -> 409
        $response = $this->postJson(route('riders.barangay', $rider), [
            'area_id' => null,
        ]);

        $response->assertStatus(409);
        $response->assertJson([
            'ok' => false,
            'count' => $openCount,
        ]);
        $this->assertNotEmpty($response->json('open_parcels'));
    }

    public function test_rider_with_open_parcels_blocks_deactivation_with_409(): void
    {
        $rider = Rider::where('initials', 'R1')->firstOrFail();

        $response = $this->postJson(route('riders.toggle-active', $rider), [
            'is_active' => false,
        ]);

        $response->assertStatus(409);
        $this->assertTrue($rider->fresh()->isActive());
    }

    public function test_rider_unassign_with_reassign_to_moves_parcels_atomically(): void
    {
        $rider = Rider::where('initials', 'R1')->firstOrFail();
        $replacement = Rider::where('initials', 'R5')->where('is_active', true)->firstOrFail();
        $openParcels = $rider->parcels()->whereIn('status', ['assigned', 'out_for_delivery'])->pluck('id');

        $response = $this->postJson(route('riders.barangay', $rider), [
            'area_id' => null,
            'reassign_to' => $replacement->id,
        ]);

        $response->assertOk();
        $this->assertNull($rider->fresh()->area_id);

        foreach ($openParcels as $pId) {
            $this->assertSame($replacement->id, Parcel::find($pId)->rider_id);
        }
    }

    public function test_bulk_assignment_endpoint(): void
    {
        $parcels = Parcel::where('status', 'sorted')->whereNotNull('area_id')->limit(3)->get();
        $rider = Rider::where('is_active', true)->whereNotNull('checked_in_at')->firstOrFail();

        $response = $this->postJson(route('assignments.bulk'), [
            'rider_id' => $rider->id,
            'parcel_ids' => $parcels->pluck('id')->all(),
        ]);

        $response->assertOk();
        $response->assertJson([
            'ok' => true,
            'count' => 3,
        ]);

        foreach ($parcels as $p) {
            $fresh = $p->fresh();
            $this->assertSame($rider->id, $fresh->rider_id);
            $this->assertSame('assigned', $fresh->status);
        }
    }

    public function test_bulk_assignment_rejects_inactive_rider_with_422(): void
    {
        $parcels = Parcel::where('status', 'sorted')->limit(2)->get();
        $inactiveRider = Rider::where('is_active', false)->firstOrFail();

        $response = $this->postJson(route('assignments.bulk'), [
            'rider_id' => $inactiveRider->id,
            'parcel_ids' => $parcels->pluck('id')->all(),
        ]);

        $response->assertStatus(422);
    }

    public function test_riders_load_endpoint_returns_correct_structure(): void
    {
        $response = $this->getJson(route('riders.load'));

        $response->assertOk();
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);

        foreach ($data as $item) {
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('label', $item);
            $this->assertArrayHasKey('open_parcels', $item);
            $this->assertArrayHasKey('status', $item);
            $this->assertArrayHasKey('disabled', $item);
            $this->assertIsInt($item['open_parcels']);
            $this->assertIsBool($item['disabled']);
        }
    }

    public function test_pickup_schedule_sorted_by_cutoff_with_flags(): void
    {
        $schedule = $this->assignmentService->getPickupSchedule();
        $this->assertNotEmpty($schedule);

        // Verify chronological sorted order
        $cutoffs = $schedule->pluck('cutoff_time')->all();
        $sortedCutoffs = $cutoffs;
        usort($sortedCutoffs, fn ($a, $b) => strtotime($a) <=> strtotime($b));
        $this->assertSame($sortedCutoffs, $cutoffs);

        // Verify courierless flag on Lumban (R10)
        $lumban = $schedule->firstWhere('code', 'R10');
        $this->assertNotNull($lumban);
        $this->assertTrue($lumban->has_no_courier);
    }

    public function test_dispatch_preview_dry_run_does_not_modify_database(): void
    {
        $stagedBefore = Parcel::where('status', 'sorted')->count();

        // 1. Preview dry run
        $response = $this->postJson(route('delivery.assignment.dispatch-all'), [
            'preview' => 1,
        ]);

        $response->assertOk();
        $response->assertJson([
            'ok' => true,
            'preview' => true,
        ]);
        $this->assertNotEmpty($response->json('areas'));

        // Assert database was NOT modified
        $this->assertSame($stagedBefore, Parcel::where('status', 'sorted')->count());

        // 2. Commit dispatch
        $commitResponse = $this->postJson(route('delivery.assignment.dispatch-all'));
        $commitResponse->assertOk();
        $commitResponse->assertJson([
            'ok' => true,
            'preview' => false,
        ]);

        $stagedAfter = Parcel::where('status', 'sorted')->count();
        $this->assertLessThan($stagedBefore, $stagedAfter);
    }
}
