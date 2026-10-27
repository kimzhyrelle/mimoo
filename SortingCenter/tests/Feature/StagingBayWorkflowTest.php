<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\DispatchManifest;
use App\Models\Parcel;
use App\Models\ParcelWeighIn;
use Database\Seeders\SortingCenterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class StagingBayWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SortingCenterSeeder::class);
    }

    public function test_sort_page_no_longer_exposes_auto_cluster(): void
    {
        $this->get(route('parcels.sort'))
            ->assertOk()
            ->assertDontSee('Auto-Cluster');

        $this->assertFalse(Route::has('parcels.sort.auto-cluster'));
    }

    public function test_sidebar_hides_notification_count_badges(): void
    {
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('nav-badge');
    }

    public function test_generated_dispatch_list_is_saved_as_a_printable_snapshot(): void
    {
        $area = Area::whereHas('rider', fn ($query) => $query->where('is_active', true))->firstOrFail();
        $parcel = Parcel::create([
            'tracking_no' => '#MANIFEST-TEST',
            'address' => 'Poblacion I, Santa Cruz',
            'status' => 'sorted',
            'area_id' => $area->id,
            'weight_kg' => 2.25,
        ]);

        $response = $this->post(route('delivery.assignment.dispatch-list.generate'));
        $manifest = DispatchManifest::latest('id')->firstOrFail();

        $response->assertRedirect(route('delivery.assignment.dispatch-list.show', $manifest));
        $entries = collect($manifest->manifest['areas'])->flatMap(fn (array $item) => $item['parcels']);
        $this->assertTrue($entries->contains('tracking_no', $parcel->tracking_no));
        $this->assertSame($entries->count(), $manifest->manifest['parcels_count']);

        $this->get(route('delivery.assignment.dispatch-list.show', $manifest))
            ->assertOk()
            ->assertSee($parcel->tracking_no)
            ->assertSee('2.25 kg');
    }

    public function test_staging_bay_records_current_weight_and_retains_weigh_in_history(): void
    {
        $area = Area::firstOrFail();
        $parcel = Parcel::create([
            'tracking_no' => '#WEIGH-TEST',
            'address' => 'Poblacion I, Santa Cruz',
            'status' => 'sorted',
            'area_id' => $area->id,
        ]);

        $this->get(route('delivery.assignment.weigh-ins'))
            ->assertOk()
            ->assertSee('Weigh-In Logs')
            ->assertSee($parcel->tracking_no);

        $this->post(route('delivery.assignment.weigh-ins.store', $parcel), ['weight_kg' => 1.75])
            ->assertRedirect();
        $this->post(route('delivery.assignment.weigh-ins.store', $parcel), ['weight_kg' => 1.8])
            ->assertRedirect();

        $this->assertSame(1.8, $parcel->fresh()->weight_kg);
        $this->assertSame(2, ParcelWeighIn::where('parcel_id', $parcel->id)->count());
        $this->assertSame(
            ['1.80', '1.75'],
            ParcelWeighIn::where('parcel_id', $parcel->id)->orderByDesc('id')->get()->map(fn (ParcelWeighIn $weighIn) => $weighIn->weight_kg)->all()
        );

        $this->get(route('delivery.assignment.weigh-ins'))
            ->assertOk()
            ->assertSee('1.80 kg')
            ->assertSee('1.75 kg');
    }

    public function test_loading_docks_dispatch_only_parcels_with_an_active_rider(): void
    {
        $assignedArea = Area::whereHas('rider', fn ($query) => $query->where('is_active', true))->firstOrFail();
        $unassignedArea = Area::whereDoesntHave('rider')->firstOrFail();
        $eligibleParcel = Parcel::create([
            'tracking_no' => '#DOCK-READY',
            'address' => 'Poblacion I, Santa Cruz',
            'status' => 'sorted',
            'area_id' => $assignedArea->id,
        ]);
        $heldParcel = Parcel::create([
            'tracking_no' => '#DOCK-HELD',
            'address' => 'Unknown barangay',
            'status' => 'sorted',
            'area_id' => $unassignedArea->id,
        ]);

        $this->post(route('delivery.assignment.dispatch-all'))->assertRedirect();

        $this->assertSame('assigned', $eligibleParcel->fresh()->status);
        $this->assertSame('sorted', $heldParcel->fresh()->status);
    }
}
