<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\MetricsService;
use Database\Seeders\SortingCenterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MetricsTest extends TestCase
{
    use RefreshDatabase;

    protected MetricsService $metrics;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SortingCenterSeeder::class);
        $this->metrics = app(MetricsService::class);
    }

    public function test_metrics_service_provides_all_nine_canonical_metrics(): void
    {
        $canonical = $this->metrics->getCanonicalMetrics();

        $expectedKeys = [
            'parcels_in_queue',
            'clusters_pending',
            'riders_on_duty',
            'riders_active',
            'riders_idle',
            'riders_in_route',
            'parcels_total_today',
            'parcels_total_range',
            'overdue_count',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $canonical, "Canonical metrics must include {$key}");
            $this->assertArrayHasKey('name', $canonical[$key]);
            $this->assertArrayHasKey('value', $canonical[$key]);
            $this->assertArrayHasKey('range_label', $canonical[$key]);
            $this->assertIsInt($canonical[$key]['value']);
            $this->assertNotEmpty($canonical[$key]['range_label']);
        }
    }

    public function test_rider_counts_satisfy_idle_plus_in_route_equals_on_duty(): void
    {
        $onDuty = $this->metrics->ridersOnDutyValue();
        $idle = $this->metrics->ridersIdleValue();
        $inRoute = $this->metrics->ridersInRouteValue();
        $active = $this->metrics->ridersActiveValue();

        $this->assertGreaterThan(0, $onDuty, 'There must be on-duty riders in seeded data');
        $this->assertGreaterThan(0, $inRoute, 'There must be in-route riders in seeded data');
        $this->assertGreaterThan(0, $idle, 'There must be idle riders in seeded data');

        $this->assertSame($onDuty, $idle + $inRoute, 'Rider counts must always satisfy idle + in_route = on_duty');
        $this->assertLessThanOrEqual($active, $onDuty, 'On-duty riders must never exceed active riders');
    }

    public function test_nav_counts_and_attention_are_consistent(): void
    {
        $nav = $this->metrics->getNavCounts();
        $att = $this->metrics->getAttention();

        $this->assertSame($nav['overdue'], $att['overdue'], 'Overdue count must agree between nav and attention');
        $this->assertSame($nav['riders_pending'], $att['pending_riders'], 'Pending riders must agree between nav and attention');
        $this->assertSame(1, $att['unassigned_clusters'], 'Unassigned Lumban cluster must be detected');
    }

    public function test_dashboard_page_numbers_agree_with_metrics_service(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('receivedToday', $this->metrics->parcelsTotalTodayValue());
        $response->assertViewHas('awaitingSort', $this->metrics->parcelsAwaitingSortValue());
        $response->assertViewHas('deliveryFailed', $this->metrics->overdueCountValue());
        $response->assertViewHas('ridersActive', $this->metrics->ridersOnDutyValue());
        $response->assertViewHas('nav_counts', $this->metrics->getNavCounts());
        $response->assertViewHas('attention', $this->metrics->getAttention());
    }

    public function test_sort_and_assign_page_numbers_agree_with_metrics_service(): void
    {
        $response = $this->get(route('parcels.sort'));

        $response->assertOk();
        $response->assertViewHas('ridersOnShift', $this->metrics->ridersOnDutyValue());
        $response->assertViewHas('idleRiders', $this->metrics->ridersIdleValue());
        $response->assertViewHas('busyRiders', $this->metrics->ridersInRouteValue());
        $response->assertViewHas('queueVolume', $this->metrics->parcelsInQueueValue());
        $response->assertViewHas('stagedParcels', $this->metrics->stagedParcelsValue());
        $response->assertViewHas('nav_counts', $this->metrics->getNavCounts());
        $response->assertViewHas('attention', $this->metrics->getAttention());

        $idle = $response->viewData('idleRiders');
        $busy = $response->viewData('busyRiders');
        $shift = $response->viewData('ridersOnShift');
        $this->assertSame($shift, $idle + $busy, 'Sort & Assign view riders must satisfy idle + busy = onShift');
    }

    public function test_delivery_monitoring_page_numbers_agree_with_metrics_service(): void
    {
        $response = $this->get(route('delivery.monitoring'));

        $response->assertOk();
        $response->assertViewHas('activeFleet', $this->metrics->ridersOnDutyValue());
        $counts = $response->viewData('counts');
        $expectedCounts = $this->metrics->deliveryMonitoringCounts();

        $this->assertSame($expectedCounts['all'], $counts['all']);
        $this->assertSame($expectedCounts['failed'], $counts['failed']);
        $this->assertSame($this->metrics->overdueCountValue(), $counts['failed']);
        $response->assertViewHas('nav_counts', $this->metrics->getNavCounts());
        $response->assertViewHas('attention', $this->metrics->getAttention());
    }

    public function test_rider_management_page_numbers_agree_with_metrics_service(): void
    {
        $response = $this->get(route('riders.index'));

        $response->assertOk();
        $response->assertViewHas('onShift', $this->metrics->ridersOnDutyValue());
        $response->assertViewHas('covered', $this->metrics->coveredAreasCount());
        $response->assertViewHas('unassigned', $this->metrics->unassignedAreasCount());
        $response->assertViewHas('nav_counts', $this->metrics->getNavCounts());
        $response->assertViewHas('attention', $this->metrics->getAttention());
    }

    public function test_reports_page_numbers_agree_with_metrics_service(): void
    {
        $response = $this->get(route('reports.index', ['preset' => 'today', 'date_from' => today()->format('Y-m-d'), 'date_to' => today()->format('Y-m-d')]));

        $response->assertOk();
        $response->assertViewHas('totalHandled', $this->metrics->parcelsTotalTodayValue());
        $response->assertViewHas('failed', $this->metrics->overdueCountValue());
        $response->assertViewHas('nav_counts', $this->metrics->getNavCounts());
    }

    public function test_api_dashboard_numbers_agree_with_metrics_service(): void
    {
        $user = User::first();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/dashboard');

        $response->assertOk();
        $data = $response->json('data');

        $this->assertSame($this->metrics->parcelsTotalTodayValue(), $data['parcels']['received_today']);
        $this->assertSame($this->metrics->parcelsAwaitingSortValue(), $data['parcels']['awaiting_sort']);
        $this->assertSame($this->metrics->readyToDispatchValue(), $data['parcels']['sorted']);
        $this->assertSame($this->metrics->overdueCountValue(), $data['parcels']['failed_today']);
        $this->assertSame($this->metrics->ridersOnDutyValue(), $data['riders']['on_shift']);
        $this->assertSame($this->metrics->ridersIdleValue(), $data['riders']['available']);
        $this->assertSame($this->metrics->ridersInRouteValue(), $data['riders']['busy']);
        $this->assertSame($data['riders']['on_shift'], $data['riders']['available'] + $data['riders']['busy']);
    }
}
