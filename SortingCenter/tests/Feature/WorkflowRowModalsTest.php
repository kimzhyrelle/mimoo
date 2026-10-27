<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Parcel;
use App\Models\Rider;
use Database\Seeders\SortingCenterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowRowModalsTest extends TestCase
{
    use RefreshDatabase;

    public function test_rider_roster_uses_view_modal_with_route_and_status_actions(): void
    {
        $this->seed(SortingCenterSeeder::class);

        $rider = Rider::whereNotNull('area_id')->where('status', 'available')->firstOrFail();

        $response = $this->get(route('riders.index'));

        $response->assertOk();
        $response->assertSee('data-row-details="off"', false);
        $response->assertSee('data-modal-open="view-rider-'.$rider->id.'"', false);
        $response->assertSee('id="view-rider-'.$rider->id.'"', false);
        $response->assertSee('action="'.route('riders.barangay', $rider).'"', false);
        $response->assertSee('action="'.route('riders.toggle-active', $rider).'"', false);
        $response->assertSee('action="'.route('riders.suspend', $rider).'"', false);
        $response->assertSee('data-view-confirm', false);
        $response->assertSee('Suspension reason');
        $response->assertSee('Save route');
    }

    public function test_suspended_rider_modal_offers_reactivation_instead_of_suspension(): void
    {
        $this->seed(SortingCenterSeeder::class);

        $rider = Rider::whereNotNull('area_id')->firstOrFail();
        $rider->update(['status' => 'suspended', 'is_active' => false]);

        $response = $this->get(route('riders.index'));

        $response->assertOk();
        $response->assertDontSee('Suspended');
        $response->assertSee('action="'.route('riders.reactivate', $rider).'"', false);
        $response->assertSee('Reactivation reason');
        $response->assertDontSee('action="'.route('riders.suspend', $rider).'"', false);
    }

    public function test_delivery_monitoring_uses_view_modal_for_status_and_archive_actions(): void
    {
        $this->seed(SortingCenterSeeder::class);

        $assigned = Parcel::where('status', 'assigned')->firstOrFail();
        $delivered = Parcel::where('status', 'delivered')->firstOrFail();

        $response = $this->get(route('delivery.monitoring'));

        $response->assertOk();
        $response->assertSee('data-row-details="off"', false);
        $response->assertSee('data-modal-open="view-parcel-'.$assigned->id.'"', false);
        $response->assertSee('id="view-parcel-'.$assigned->id.'"', false);
        $response->assertSee('action="'.route('delivery.monitoring.advance', $assigned).'"', false);
        $response->assertSee('action="'.route('shipments.archive.parcel', $delivered).'"', false);
        $response->assertSee('Show in timeline');
        $response->assertSee('Custody timeline');
        $response->assertDontSee('confirmAdvance', false);
        $response->assertDontSee('confirmArchive', false);
    }

    public function test_delivery_assignment_uses_shared_view_and_dispatch_modals(): void
    {
        $this->seed(SortingCenterSeeder::class);

        $area = Area::whereHas('rider', fn ($query) => $query->where('is_active', true))
            ->whereHas('parcels', fn ($query) => $query->where('status', 'sorted'))
            ->firstOrFail();

        $response = $this->get(route('delivery.assignment'));

        $response->assertOk();
        $response->assertSee('data-row-details="off"', false);
        $response->assertSee('data-modal-open="view-area-'.$area->id.'"', false);
        $response->assertSee('id="view-area-'.$area->id.'"', false);
        $response->assertSee('action="'.route('delivery.assignment.dispatch-area', $area).'"', false);
        $response->assertSee('data-view-confirm', false);
        $response->assertSee('data-modal-open="confirmDispatchAll"', false);
        $response->assertSee('action="'.route('delivery.assignment.dispatch-all').'"', false);
        $response->assertDontSee('openViewModal', false);
        $response->assertDontSee('openDispatchModal', false);
        $response->assertDontSee('id="viewModalBackdrop"', false);
    }
}
