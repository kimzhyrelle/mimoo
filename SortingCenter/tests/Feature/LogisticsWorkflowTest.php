<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\IssueReport;
use App\Models\Parcel;
use App\Models\Rider;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Models\User;
use Database\Seeders\SortingCenterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LogisticsWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SortingCenterSeeder::class);
    }

    public function test_checkout_quote_uses_seeded_zone_and_weight_tier(): void
    {
        $this->postJson('/api/shipping/quote', [
            'address' => 'Poblacion I, Santa Cruz North, Laguna',
            'weight_kg' => 3,
        ])->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('data.serviceable', true)
            ->assertJsonPath('data.zone', 'Santa Cruz North')
            ->assertJsonPath('data.chargeable_weight', 3)
            ->assertJsonPath('data.fee', 69);
    }

    public function test_checkout_quote_matches_a_barangay_without_the_zone_name(): void
    {
        $this->postJson('/api/shipping/quote', [
            'address' => '12 Rizal St., Purok 3, Poblacion I',
            'weight_kg' => 3,
        ])->assertOk()
            ->assertJsonPath('data.serviceable', true)
            ->assertJsonPath('data.zone', 'Santa Cruz North')
            ->assertJsonPath('data.fee', 69);
    }

    public function test_public_tracking_accepts_a_tracking_number_without_the_hash_prefix(): void
    {
        $this->getJson('/api/shipments/1009/tracking')
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('data.tracking_no', '#1009');
    }

    public function test_support_ticket_can_be_created_replied_closed_and_reopened(): void
    {
        $response = $this->postJson(route('support.store'), [
            'category' => 'Parcel issue',
            'priority' => 'High',
            'subject' => 'Parcel delayed at the hub',
            'description' => 'This parcel has been sitting at the hub for more than a day.',
            'related_reference' => '#1001',
        ])->assertOk()->assertJsonPath('ok', true);

        $report = IssueReport::where('ticket_no', $response->json('report.ticket_no'))->firstOrFail();
        $this->assertSame('Open', $report->status);

        $this->get(route('support.index'))
            ->assertOk()
            ->assertSee($report->ticket_no)
            ->assertSee($report->subject)
            ->assertSee('data-row-details="off"', false)
            ->assertSee('>Action</th>', false)
            ->assertSee('class="row-view-btn"', false)
            ->assertSee('href="'.route('support.show', $report).'"', false)
            ->assertSee('>View</a>', false);

        $this->postJson(route('support.replies.store', $report), ['body' => 'We are checking with the sorting team.'])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->postJson(route('support.status', $report), ['status' => 'Closed'])
            ->assertStatus(422);

        $this->postJson(route('support.status', $report), ['status' => 'Closed', 'reason' => 'Parcel located and re-dispatched.'])
            ->assertOk();

        $this->assertSame('Closed', $report->fresh()->status);

        $this->postJson(route('support.replies.store', $report), ['body' => 'Any update?'])
            ->assertStatus(422);

        $this->postJson(route('support.status', $report), ['status' => 'Open', 'reason' => 'Buyer requested another update.'])
            ->assertOk();

        $this->postJson(route('support.replies.store', $report), ['body' => 'We re-dispatched the parcel today.'])
            ->assertOk();
    }

    public function test_zone_rate_update_keeps_the_weight_ceiling(): void
    {
        $this->postJson(route('zones.store'), [
            'name' => 'Lucena City',
            'region' => 'Quezon',
            'areas' => 'R01, R02',
            'is_serviceable' => true,
        ])->assertOk();

        $zone = ShippingZone::where('name', 'Lucena City')->firstOrFail();

        $this->postJson(route('rates.store', $zone), [
            'base_fee' => 60,
            'per_kg_fee' => 15,
            'min_weight_kg' => 1,
            'max_weight_kg' => 10,
        ])->assertOk();

        $rate = ShippingRate::where('shipping_zone_id', $zone->id)->firstOrFail();

        $this->postJson(route('rates.update', [$zone, $rate]), [
            'base_fee' => 70,
            'per_kg_fee' => 16,
            'min_weight_kg' => 1,
        ])->assertOk();

        $this->assertSame('10.00', $rate->fresh()->max_weight_kg);
    }

    public function test_zones_rate_table_disables_automatic_view_actions(): void
    {
        $this->get(route('zones.index'))
            ->assertOk()
            ->assertSee('data-row-details="off"', false)
            ->assertSee('Save Zone')
            ->assertSee('Add Tier');
    }

    public function test_cod_remittance_confirmation_marks_parcels_as_remitted(): void
    {
        $rider = Rider::where('initials', 'R5')->firstOrFail();
        $parcel = Parcel::create([
            'tracking_no' => '#COD-TEST',
            'address' => 'Purok 1, Santa Cruz',
            'seller_name' => 'COD Seller',
            'buyer_name' => 'COD Buyer',
            'status' => 'delivered',
            'rider_id' => $rider->id,
            'delivered_at' => now(),
            'cod_amount' => 250,
            'cod_collected' => 250,
            'cod_remitted' => false,
            'is_prepaid' => false,
        ]);

        $response = $this->postJson(route('cod.remittances.store'), [
            'rider_id' => $rider->id,
            'collected' => 250,
            'reference' => 'OR-250',
        ])->assertOk()->assertJsonPath('ok', true);

        $this->postJson(route('cod.remittances.confirm', $response->json('data.id')))
            ->assertOk();

        $this->assertTrue((bool) $parcel->fresh()->cod_remitted);
        $this->assertNotNull($parcel->fresh()->cod_remitted_at);
    }

    public function test_unremitted_cod_list_has_its_own_scroll_container(): void
    {
        $this->get(route('cod.index'))
            ->assertOk()
            ->assertSee('max-h-[70vh] divide-y divide-border overflow-y-auto', false)
            ->assertSee('Unremitted COD');
    }

    public function test_dispatch_api_assigns_and_suspends_without_type_errors(): void
    {
        Sanctum::actingAs(User::where('role', 'staff')->firstOrFail());

        $area = Area::where('code', 'R01')->firstOrFail();
        $parcel = Parcel::create([
            'tracking_no' => '#DISPATCH-TEST',
            'address' => 'Poblacion I',
            'seller_name' => 'Dispatch Seller',
            'status' => 'sorted',
            'area_id' => $area->id,
        ]);

        $this->postJson('/api/dispatch/'.$parcel->id.'/auto-assign')
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertNotNull($parcel->fresh()->rider_id);
        $this->assertSame('assigned', $parcel->fresh()->status);

        $replacement = Rider::where('initials', 'R12')->firstOrFail();

        $this->postJson('/api/dispatch/'.$parcel->id.'/reassign', ['rider_id' => $replacement->id])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertSame($replacement->id, $parcel->fresh()->rider_id);

        $rider = $parcel->fresh()->rider;
        $this->postJson(route('riders.suspend', $rider), [
            'reason' => 'Vehicle documents have expired.',
        ])->assertOk()->assertJsonPath('ok', true);

        $parcel->refresh();
        $this->assertNull($parcel->rider_id);
        $this->assertSame('ready_for_pickup', $parcel->status);
        $this->assertFalse($rider->fresh()->isActive());
    }

    public function test_reports_xlsx_export_returns_a_real_zip_workbook(): void
    {
        $response = $this->get(route('reports.index', [
            'type' => 'parcels',
            'preset' => 'today',
            'format' => 'xlsx',
        ]));

        $response->assertOk()->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $path = $response->baseResponse->getFile()->getPathname();
        $this->assertStringStartsWith('PK', (string) file_get_contents($path));
    }
}
