<?php

namespace Tests\Feature;

use App\Models\Parcel;
use App\Models\ParcelStatusLog;
use App\Models\Rider;
use App\Services\MetricsService;
use App\Services\ParcelStatusService;
use Database\Seeders\SortingCenterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ParcelStatusTest extends TestCase
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

    public function test_valid_transitions_succeed_and_write_status_logs(): void
    {
        $parcel = Parcel::where('status', 'dropped_off')->firstOrFail();

        // 1. dropped_off -> awaiting_sort
        $res1 = $this->statusService->transition($parcel, 'awaiting_sort', 'Scan at inbound dock');
        $this->assertTrue($res1['ok']);
        $this->assertSame('awaiting_sort', $parcel->fresh()->status);
        $this->assertNotEmpty($res1['undo_token']);

        $log1 = ParcelStatusLog::where('undo_token', $res1['undo_token'])->first();
        $this->assertNotNull($log1);
        $this->assertSame('dropped_off', $log1->from);
        $this->assertSame('awaiting_sort', $log1->to);

        // 2. awaiting_sort -> sorted
        $res2 = $this->statusService->transition($parcel, 'sorted', 'Sorted to area');
        $this->assertTrue($res2['ok']);
        $this->assertSame('sorted', $parcel->fresh()->status);

        // 3. sorted -> assigned (with active rider)
        $activeRider = Rider::where('is_active', true)->firstOrFail();
        $parcel->update(['rider_id' => $activeRider->id]);
        $res3 = $this->statusService->transition($parcel, 'assigned', 'Assigned to rider');
        $this->assertTrue($res3['ok']);
        $this->assertSame('assigned', $parcel->fresh()->status);

        // 4. assigned -> out_for_delivery
        $res4 = $this->statusService->transition($parcel, 'out_for_delivery');
        $this->assertTrue($res4['ok']);
        $this->assertSame('out_for_delivery', $parcel->fresh()->status);

        // 5. out_for_delivery -> delivered
        $res5 = $this->statusService->transition($parcel, 'delivered');
        $this->assertTrue($res5['ok']);
        $this->assertSame('delivered', $parcel->fresh()->status);
        $this->assertNotNull($parcel->fresh()->delivered_at);
    }

    public function test_delivered_parcel_is_locked_and_rejects_transitions(): void
    {
        $deliveredParcel = Parcel::where('status', 'delivered')->firstOrFail();

        $this->expectException(ValidationException::class);
        $this->statusService->transition($deliveredParcel, 'out_for_delivery');
    }

    public function test_delivered_parcel_rejects_reassignment(): void
    {
        $deliveredParcel = Parcel::where('status', 'delivered')->firstOrFail();
        $newRider = Rider::where('is_active', true)->where('id', '!=', $deliveredParcel->rider_id)->firstOrFail();

        // Testing HTTP endpoint
        $response = $this->post(route('parcels.sort.assign-rider', $deliveredParcel));
        $response->assertSessionHasErrors();
        $this->assertNotSame($newRider->id, $deliveredParcel->fresh()->rider_id);
    }

    public function test_cannot_dispatch_to_out_for_delivery_without_active_rider(): void
    {
        $parcel = Parcel::where('status', 'sorted')->firstOrFail();
        $parcel->update(['rider_id' => null, 'status' => 'assigned']);

        // Without rider
        try {
            $this->statusService->transition($parcel, 'out_for_delivery');
            $this->fail('Should have failed without assigned rider');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('status', $e->errors());
        }

        // With inactive rider
        $inactiveRider = Rider::where('is_active', false)->firstOrFail();
        $parcel->update(['rider_id' => $inactiveRider->id]);

        try {
            $this->statusService->transition($parcel, 'out_for_delivery');
            $this->fail('Should have failed with inactive rider');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('status', $e->errors());
        }
    }

    public function test_invalid_transitions_are_rejected(): void
    {
        $parcel = Parcel::where('status', 'sorted')->firstOrFail();
        $parcel->update(['status' => 'at_hub']);

        // at_hub -> delivered is direct illegal leap
        $this->expectException(ValidationException::class);
        $this->statusService->transition($parcel, 'delivered');
    }

    public function test_undo_reverts_status_within_ten_minutes(): void
    {
        $parcel = Parcel::where('status', 'dropped_off')->firstOrFail();

        $res = $this->statusService->transition($parcel, 'awaiting_sort');
        $token = $res['undo_token'];
        $this->assertSame('awaiting_sort', $parcel->fresh()->status);

        // Undo
        $undoRes = $this->statusService->undo($token);
        $this->assertTrue($undoRes['ok']);
        $this->assertSame('dropped_off', $parcel->fresh()->status);

        // Cannot undo again
        $this->expectException(ValidationException::class);
        $this->statusService->undo($token);
    }

    public function test_undo_rejects_if_parcel_has_subsequent_transition(): void
    {
        $parcel = Parcel::where('status', 'dropped_off')->firstOrFail();

        $res1 = $this->statusService->transition($parcel, 'awaiting_sort');
        $token1 = $res1['undo_token'];

        // Second transition
        $this->statusService->transition($parcel, 'sorted');

        // Trying to undo transition 1 must fail
        $this->expectException(ValidationException::class);
        $this->statusService->undo($token1);
    }

    public function test_undo_rejects_after_ten_minutes(): void
    {
        $parcel = Parcel::where('status', 'dropped_off')->firstOrFail();

        $res = $this->statusService->transition($parcel, 'awaiting_sort');
        $token = $res['undo_token'];

        // Travel 11 minutes into future
        $this->travel(11)->minutes();

        $this->expectException(ValidationException::class);
        $this->statusService->undo($token);
    }

    public function test_shipments_status_endpoint_returns_json(): void
    {
        $parcel = Parcel::where('status', 'dropped_off')->firstOrFail();

        $response = $this->postJson(route('shipments.status'), [
            'parcel_id' => $parcel->id,
            'status' => 'awaiting_sort',
            'reason' => 'API test transition',
        ]);

        $response->assertOk();
        $response->assertJson([
            'ok' => true,
        ]);
        $this->assertNotEmpty($response->json('undo_token'));
    }

    public function test_shipments_undo_endpoint_via_post(): void
    {
        $parcel = Parcel::where('status', 'dropped_off')->firstOrFail();
        $res = $this->statusService->transition($parcel, 'awaiting_sort');

        $response = $this->postJson(route('shipments.undo'), [
            'token' => $res['undo_token'],
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => true]);
        $this->assertSame('dropped_off', $parcel->fresh()->status);
    }

    public function test_archive_and_restore_endpoints(): void
    {
        $parcel = Parcel::where('status', 'sorted')->firstOrFail();
        $initialQueue = $this->metrics->parcelsInQueueValue();

        // 1. Archive
        $response = $this->postJson(route('shipments.archive'), [
            'parcel_id' => $parcel->id,
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => true]);
        $this->assertSoftDeleted('parcels', ['id' => $parcel->id]);
        $this->assertNotNull(Parcel::withTrashed()->find($parcel->id)->archived_at);

        // Excluded from standard metrics query
        $newQueue = $this->metrics->parcelsInQueueValue();
        $this->assertSame($initialQueue - 1, $newQueue);

        // 2. Restore
        $restoreRes = $this->postJson(route('shipments.restore'), [
            'parcel_id' => $parcel->id,
        ]);

        $restoreRes->assertOk();
        $restoreRes->assertJson(['ok' => true]);
        $this->assertNotSoftDeleted('parcels', ['id' => $parcel->id]);
        $this->assertNull(Parcel::find($parcel->id)->archived_at);
        $this->assertSame($initialQueue, $this->metrics->parcelsInQueueValue());
    }

    public function test_bulk_archive_endpoint(): void
    {
        $parcels = Parcel::where('status', 'sorted')->limit(3)->get();
        $ids = $parcels->pluck('id')->all();

        $response = $this->postJson(route('parcels.sort.bulk-archive'), [
            'ids' => $ids,
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => true, 'count' => 3]);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('parcels', ['id' => $id]);
        }
    }

    public function test_activity_feed_timeline_format(): void
    {
        $activity = $this->statusService->getActivityFeed(5);

        $this->assertIsArray($activity);
        $this->assertNotEmpty($activity);

        foreach ($activity as $item) {
            $this->assertArrayHasKey('icon', $item);
            $this->assertArrayHasKey('text', $item);
            $this->assertArrayHasKey('url', $item);
            $this->assertArrayHasKey('at', $item);
            $this->assertIsString($item['icon']);
            $this->assertIsString($item['text']);
            $this->assertIsString($item['url']);
            $this->assertIsString($item['at']);
        }
    }
}
