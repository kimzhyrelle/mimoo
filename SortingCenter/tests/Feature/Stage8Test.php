<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Parcel;
use App\Models\Rider;
use App\Models\User;
use Database\Seeders\SortingCenterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class Stage8Test extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_recent_shipments_sort_dropdown_changes_order_and_preserves_selection(): void
    {
        Parcel::create([
            'tracking_no' => '#DASH-SORT-B',
            'address' => 'Second Street',
            'seller_name' => 'Seller B',
            'status' => 'dropped_off',
            'received_at' => now(),
        ]);
        Parcel::create([
            'tracking_no' => '#DASH-SORT-A',
            'address' => 'First Street',
            'seller_name' => 'Seller A',
            'status' => 'dropped_off',
            'received_at' => now()->subMinute(),
        ]);

        $response = $this->get(route('dashboard', ['shipment_sort' => 'tracking']));
        $content = $response->assertOk()->getContent();

        $response->assertSee('value="tracking" selected', false);
        $this->assertLessThan(strpos($content, '#DASH-SORT-B'), strpos($content, '#DASH-SORT-A'));
    }

    public function test_parcel_filters_search_status_area_date_and_sorting(): void
    {
        $north = Area::create(['name' => 'North', 'code' => 'N8']);
        $south = Area::create(['name' => 'South', 'code' => 'S8']);

        $old = Parcel::create([
            'tracking_no' => '#ZZ-S8-A',
            'address' => 'Unique Zebra Street',
            'seller_name' => 'Zebra Seller',
            'status' => 'awaiting_sort',
            'area_id' => $north->id,
            'received_at' => now()->subDays(3),
            'weight_kg' => 12,
        ]);

        $new = Parcel::create([
            'tracking_no' => '#ZZ-S8-B',
            'address' => 'Second Street',
            'seller_name' => 'Other Seller',
            'status' => 'sorted',
            'area_id' => $south->id,
            'received_at' => now(),
            'weight_kg' => 2,
        ]);

        $this->assertSame([$old->id], Parcel::listFilters(['q' => 'Zebra'])->pluck('id')->all());
        $this->assertSame([$new->id], Parcel::listFilters(['status' => 'sorted'])->pluck('id')->all());
        $this->assertSame([$old->id], Parcel::listFilters(['area_id' => $north->id])->pluck('id')->all());
        $this->assertSame(
            [$new->id],
            Parcel::listFilters(['from' => today()->toDateString(), 'to' => today()->toDateString()])->pluck('id')->all()
        );

        $this->assertSame([$old->id, $new->id], Parcel::listFilters(['sort' => 'tracking'])->pluck('id')->all());
        $this->assertSame([$old->id, $new->id], Parcel::listFilters(['sort' => 'heaviest'])->pluck('id')->all());
        $this->assertSame([$old->id, $new->id], Parcel::listFilters(['sort' => 'oldest'])->pluck('id')->all());
    }

    public function test_conversation_and_rider_filters_search_status_and_sorting(): void
    {
        $area = Area::create(['name' => 'North', 'code' => 'N8']);

        $riderConversation = Conversation::create([
            'type' => 'rider',
            'name' => 'Zed Rider',
            'initials' => 'ZR',
            'last_message_at' => now(),
        ]);
        $sellerConversation = Conversation::create([
            'type' => 'seller',
            'name' => 'Amy Seller',
            'initials' => 'AS',
            'last_message_at' => now()->subHour(),
        ]);

        Message::create([
            'conversation_id' => $riderConversation->id,
            'sender' => 'them',
            'body' => 'The parcel is delayed',
        ]);

        $this->assertSame([$riderConversation->id], Conversation::listFilters(['q' => 'delayed'])->pluck('id')->all());
        $this->assertSame([$riderConversation->id], Conversation::listFilters(['status' => 'rider'])->pluck('id')->all());
        $this->assertSame(
            [$sellerConversation->id, $riderConversation->id],
            Conversation::listFilters(['sort' => 'name'])->pluck('id')->all()
        );

        $active = Rider::create([
            'name' => 'Unique Active',
            'initials' => 'UA',
            'area_id' => $area->id,
            'status' => 'available',
            'is_active' => true,
            'checked_in_at' => now(),
        ]);
        $inactive = Rider::create([
            'name' => 'Unique Inactive',
            'initials' => 'UI',
            'area_id' => $area->id,
            'status' => 'offline',
            'is_active' => false,
        ]);

        $this->assertSame([$active->id], Rider::listFilters(['status' => 'active'])->pluck('id')->all());
        $this->assertSame([$inactive->id], Rider::listFilters(['status' => 'inactive'])->pluck('id')->all());
        $this->assertSame([$active->id], Rider::listFilters(['q' => 'Unique Active'])->pluck('id')->all());
        $this->assertSame(
            [$active->id, $inactive->id],
            Rider::listFilters(['sort' => 'name'])->pluck('id')->all()
        );
    }

    public function test_riders_api_supports_filters_counts_and_fleet_meta(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        Sanctum::actingAs($user);

        $area = Area::create(['name' => 'North', 'code' => 'N8']);

        Rider::create([
            'name' => 'Unique Active',
            'initials' => 'UA',
            'area_id' => $area->id,
            'status' => 'available',
            'is_active' => true,
            'checked_in_at' => now(),
        ]);
        Rider::create([
            'name' => 'Other Rider',
            'initials' => 'OR',
            'area_id' => $area->id,
            'status' => 'offline',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/riders?q=Unique&status=active&sort=name');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Unique Active')
            ->assertJsonPath('data.0.open_parcels', 0)
            ->assertJsonPath('data.0.out_for_delivery_parcels', 0)
            ->assertJsonPath('data.0.delivered_today', 0)
            ->assertJsonStructure([
                'meta' => [
                    'fleet' => ['riders_on_duty', 'riders_active', 'riders_idle', 'riders_in_route'],
                ],
            ]);
    }

    public function test_courier_can_view_claim_and_advance_parcels(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        Sanctum::actingAs($user);

        $area = Area::create(['name' => 'North', 'code' => 'N8']);
        $rider = Rider::create([
            'name' => 'Courier One',
            'initials' => 'C1',
            'area_id' => $area->id,
            'status' => 'available',
            'is_active' => true,
            'checked_in_at' => now(),
        ]);
        $parcel = Parcel::create([
            'tracking_no' => '#COURIER-1',
            'address' => 'Courier Lane',
            'status' => 'sorted',
            'area_id' => $area->id,
            'received_at' => now(),
        ]);

        $this->getJson("/api/courier/{$rider->id}")
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('data.counts.open', 0)
            ->assertJsonStructure(['meta' => ['fleet' => ['riders_on_duty', 'riders_active', 'riders_idle', 'riders_in_route']]]);

        $this->postJson("/api/courier/{$rider->id}/claim", ['parcel_id' => $parcel->id])
            ->assertOk()
            ->assertJsonPath('data.parcel.status', 'assigned')
            ->assertJsonPath('data.parcel.rider.id', $rider->id)
            ->assertJsonPath('data.counts.assigned', 1);

        $this->assertSame($rider->id, $parcel->fresh()->rider_id);
        $this->assertSame('assigned', $parcel->fresh()->status);

        $this->postJson("/api/courier/{$rider->id}/advance", ['parcel_id' => $parcel->id])
            ->assertOk()
            ->assertJsonPath('data.parcel.status', 'out_for_delivery')
            ->assertJsonPath('data.counts.out_for_delivery', 1);

        $this->postJson("/api/courier/{$rider->id}/advance", ['parcel_id' => $parcel->id])
            ->assertOk()
            ->assertJsonPath('data.parcel.status', 'delivered')
            ->assertJsonPath('data.counts.delivered_today', 1);

        $this->assertNotNull($parcel->fresh()->delivered_at);
    }

    public function test_courier_claim_rules_and_rider_ownership_are_enforced(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $north = Area::create(['name' => 'North', 'code' => 'N8']);
        $south = Area::create(['name' => 'South', 'code' => 'S8']);

        $rider = Rider::create([
            'name' => 'Courier One',
            'initials' => 'C1',
            'area_id' => $north->id,
            'status' => 'available',
            'is_active' => true,
            'checked_in_at' => now(),
        ]);
        $otherRider = Rider::create([
            'name' => 'Courier Two',
            'initials' => 'C2',
            'area_id' => $north->id,
            'status' => 'available',
            'is_active' => true,
            'checked_in_at' => now(),
        ]);

        $wrongArea = Parcel::create([
            'tracking_no' => '#COURIER-WRONG-AREA',
            'address' => 'South Lane',
            'status' => 'sorted',
            'area_id' => $south->id,
            'received_at' => now(),
        ]);
        $taken = Parcel::create([
            'tracking_no' => '#COURIER-TAKEN',
            'address' => 'North Lane',
            'status' => 'assigned',
            'area_id' => $north->id,
            'rider_id' => $otherRider->id,
            'received_at' => now(),
        ]);

        Sanctum::actingAs($staff);

        $this->postJson("/api/courier/{$rider->id}/claim", ['parcel_id' => $wrongArea->id])
            ->assertStatus(422)
            ->assertJsonPath('ok', false);

        $this->postJson("/api/courier/{$rider->id}/claim", ['parcel_id' => $taken->id])
            ->assertStatus(422)
            ->assertJsonPath('ok', false);

        $riderUser = User::factory()->create(['role' => 'rider']);
        $rider->update(['user_id' => $riderUser->id]);
        Sanctum::actingAs($riderUser);

        $this->getJson("/api/courier/{$otherRider->id}")->assertForbidden();
        $this->getJson("/api/courier/{$rider->id}")->assertOk();
    }

    public function test_listing_pages_accept_stage8_filters(): void
    {
        $this->seed(SortingCenterSeeder::class);

        $area = Area::firstOrFail();
        $rider = Rider::firstOrFail();
        $from = today()->subDays(7)->toDateString();
        $to = today()->toDateString();

        $this->get(route('parcels.incoming', [
            'q' => '#',
            'area_id' => $area->id,
            'sort' => 'oldest',
            'from' => $from,
            'to' => $to,
        ]))->assertOk();

        $this->get(route('parcels.sort', [
            'q' => '#',
            'status' => 'sorted',
            'area_id' => $area->id,
            'rider' => $rider->id,
            'sort' => 'heaviest',
            'from' => $from,
            'to' => $to,
        ]))->assertOk();

        $this->get(route('riders.index', [
            'q' => 'a',
            'status' => 'active',
            'area_id' => $area->id,
            'sort' => 'name',
            'from' => $from,
            'to' => $to,
        ]))->assertOk();

        $this->get(route('delivery.monitoring', [
            'filter' => 'out',
            'q' => '#',
            'rider' => $rider->id,
            'sort' => 'assigned_newest',
            'from' => $from,
            'to' => $to,
        ]))->assertOk();

        $this->get(route('chat.index', ['q' => 'a', 'sort' => 'recent']))->assertOk();
    }

    public function test_stage8_parcel_indexes_exist(): void
    {
        $indexes = collect(Schema::getIndexes('parcels'))->pluck('name')->all();

        $this->assertContains('parcels_status_received_at_index', $indexes);
        $this->assertContains('parcels_status_assigned_at_index', $indexes);
        $this->assertContains('parcels_status_area_id_index', $indexes);
        $this->assertContains('parcels_status_rider_id_sla_due_at_index', $indexes);
        $this->assertContains('parcels_rider_id_status_index', $indexes);
    }
}
