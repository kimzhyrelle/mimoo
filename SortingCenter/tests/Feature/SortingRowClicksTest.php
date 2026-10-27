<?php

namespace Tests\Feature;

use App\Models\Parcel;
use Database\Seeders\SortingCenterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SortingRowClicksTest extends TestCase
{
    use RefreshDatabase;

    public function test_sort_parcels_clusters_have_no_view_action_but_parcels_keep_explicit_view_modals(): void
    {
        $this->seed(SortingCenterSeeder::class);

        $parcel = Parcel::where('tracking_no', '#1044')->firstOrFail();

        $response = $this->get(route('parcels.sort', ['view' => 'table']));

        $response->assertOk();
        $response->assertSee('data-row-details="off"', false);
        $response->assertDontSee('data-modal-open="view-cluster-', false);
        $response->assertSee('data-modal-open="view-parcel-'.$parcel->id.'"', false);
        $response->assertSee('id="view-parcel-'.$parcel->id.'"', false);
        $response->assertSee('Sort to Lumban');
        $response->assertSee('The address matches <strong>Lumban</strong>', false);
    }

    public function test_sort_action_confirms_the_matched_barangay_and_returns_a_toast(): void
    {
        $this->seed(SortingCenterSeeder::class);

        $parcel = Parcel::where('tracking_no', '#1044')->firstOrFail();

        $response = $this->post(route('parcels.sort.determine-area', $parcel));

        $response->assertRedirect();
        $response->assertSessionHas('status');
        $response->assertSessionHas('toast', '#1044 sorted to Lumban.');

        $parcel->refresh();

        $this->assertSame('sorted', $parcel->status);
        $this->assertSame('Lumban', $parcel->area?->name);
    }

    public function test_incoming_drop_off_queue_keeps_scan_only_and_disables_legacy_row_details(): void
    {
        $this->seed(SortingCenterSeeder::class);

        $parcel = Parcel::where('tracking_no', '#1038')->firstOrFail();

        $response = $this->get(route('parcels.incoming'));

        $response->assertOk();
        $response->assertSee('Drop-off queue');
        $response->assertSee('data-row-details="off"', false);
        $response->assertSee('action="'.route('parcels.incoming.scan', $parcel).'"', false);
        $response->assertDontSee('data-modal-open="view-parcel-'.$parcel->id.'"', false);
    }
}
