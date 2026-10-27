<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Parcel;
use App\Services\ReportService;
use Database\Seeders\SortingCenterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ReportService $reportService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SortingCenterSeeder::class);
        $this->reportService = app(ReportService::class);
    }

    public function test_different_report_types_return_different_kpis(): void
    {
        $from = now()->startOfMonth();
        $to = now()->endOfDay();

        $types = ['parcels', 'delivery', 'riders', 'branches', 'overdue'];
        $kpiKeysByType = [];

        foreach ($types as $type) {
            $report = $this->reportService->generate($type, $from, $to);

            $this->assertArrayHasKey('kpis', $report);
            $this->assertArrayHasKey('rows', $report);
            $this->assertArrayHasKey('chart', $report);
            $this->assertArrayHasKey('range_label', $report);

            $this->assertCount(4, $report['kpis'], "Report type {$type} must return 4 KPIs.");

            $keys = array_column($report['kpis'], 'key');
            $kpiKeysByType[$type] = $keys;
        }

        // Assert all 5 report types have distinct KPI keys
        $allUnique = array_unique(array_map('serialize', $kpiKeysByType));
        $this->assertCount(5, $allUnique, 'Every report type must return a completely distinct set of KPIs.');

        // Specific key checks
        $this->assertContains('total_inbound', $kpiKeysByType['parcels']);
        $this->assertContains('delivery_rate', $kpiKeysByType['delivery']);
        $this->assertContains('active_couriers', $kpiKeysByType['riders']);
        $this->assertContains('hub_throughput', $kpiKeysByType['branches']);
        $this->assertContains('overdue_exceptions', $kpiKeysByType['overdue']);
    }

    public function test_reports_show_route_with_preset_today_passes_report_to_view(): void
    {
        $response = $this->get(route('reports.show', ['preset' => 'today']));

        $response->assertStatus(200);
        $response->assertViewHas('report');

        $report = $response->viewData('report');
        $this->assertIsArray($report);
        $this->assertArrayHasKey('kpis', $report);
        $this->assertSame('Today', $report['range_label']);
    }

    public function test_reports_show_route_with_7d_preset(): void
    {
        $response = $this->get(route('reports.show', ['preset' => '7d']));

        $response->assertStatus(200);
        $response->assertViewHas('report');

        $report = $response->viewData('report');
        $this->assertIsArray($report);
        $this->assertNotEmpty($report['range_label']);
    }

    public function test_reports_show_route_with_month_preset(): void
    {
        $response = $this->get(route('reports.show', ['preset' => 'month']));

        $response->assertStatus(200);
        $response->assertViewHas('report');
    }

    public function test_reports_invalid_date_range_validation(): void
    {
        // When end date is before start date
        $response = $this->get(route('reports.show', [
            'from' => '2026-10-25',
            'to' => '2026-10-20',
        ]));

        $response->assertSessionHasErrors(['date_to']);
    }

    public function test_reports_export_csv(): void
    {
        $response = $this->get(route('reports.show', [
            'type' => 'parcels',
            'format' => 'csv',
        ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('attachment;', (string) $response->headers->get('content-disposition'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Mimoo Sorting Center Report', $content);
        $this->assertStringContainsString('Total Inbound', $content);
        $this->assertStringContainsString('Parcels processed', $content);
    }

    public function test_reports_export_pdf(): void
    {
        $response = $this->get(route('reports.show', [
            'type' => 'delivery',
            'format' => 'pdf',
        ]));

        $response->assertStatus(200);
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringContainsString('attachment;', (string) $response->headers->get('content-disposition'));
        $this->assertGreaterThan(500, strlen($response->getContent()));
    }

    public function test_reports_json_api_response(): void
    {
        $response = $this->getJson(route('reports.show', [
            'type' => 'riders',
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'type',
            'title',
            'kpis' => [
                '*' => ['key', 'label', 'value', 'sub'],
            ],
            'rows',
            'chart' => ['labels', 'values'],
            'range_label',
        ]);
    }

    public function test_reports_index_and_show_alias(): void
    {
        $resIndex = $this->get(route('reports.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertViewHas('report');

        $resShow = $this->get(route('reports.show'));
        $resShow->assertStatus(200);
        $resShow->assertViewHas('report');
    }

    public function test_delivery_and_branch_reports_respect_barangay_and_carrier_filters(): void
    {
        $area = Area::where('code', 'R03')->firstOrFail();
        $from = now()->startOfDay();
        $to = now()->endOfDay();
        $expected = Parcel::where('area_id', $area->id)
            ->where('carrier', 'jnt')
            ->whereBetween('received_at', [$from, $to])
            ->count();

        foreach (['delivery', 'branches'] as $type) {
            $report = $this->reportService->generate($type, $from, $to, (string) $area->id, 'jnt');
            $rows = collect($report['rows']);

            $this->assertCount(1, $rows, "{$type} should only include the selected barangay.");
            $this->assertSame('Palasan', $rows->first()['name']);
            $this->assertSame($expected, $rows->sum('count'));
            $this->assertCount(1, $report['chart']['labels']);
            $this->assertSame('Palasan', $report['chart']['labels'][0]);
        }
    }

    public function test_rider_report_respects_barangay_and_carrier_filters(): void
    {
        $area = Area::where('code', 'R03')->firstOrFail();
        $from = now()->startOfDay();
        $to = now()->endOfDay();

        $report = $this->reportService->generate('riders', $from, $to, (string) $area->id, 'jnt');
        $rows = collect($report['rows']);

        $this->assertCount(1, $rows);
        $this->assertSame('A. Santos', $rows->first()['rider']->name);

        $expected = Parcel::where('rider_id', $rows->first()['rider']->id)
            ->where('carrier', 'jnt')
            ->whereBetween('received_at', [$from, $to])
            ->count();

        $this->assertSame($expected, $rows->first()['total']);
    }

    public function test_daily_average_uses_an_inclusive_calendar_day_count(): void
    {
        $from = Carbon::parse('2026-10-01')->startOfDay();
        $to = Carbon::parse('2026-10-25')->endOfDay();

        $report = $this->reportService->generate('parcels', $from, $to);

        $dailyAverage = collect($report['kpis'])->firstWhere('key', 'daily_avg');

        $this->assertSame('Across 25 operating days', $dailyAverage['sub']);
        $this->assertMatchesRegularExpression('/^\d+(\.\d)?$/', (string) $dailyAverage['value']);
    }

    public function test_reports_page_renders_type_specific_kpis_and_chart(): void
    {
        $response = $this->get(route('reports.index', ['type' => 'riders', 'preset' => 'month']));

        $response->assertStatus(200);
        $response->assertSee('Active Couriers');
        $response->assertSee('Fleet Deliveries');
        $response->assertSee('Parcels Handled by Rider');
        $response->assertSee('reportChart');
        $response->assertSee('format=pdf');
        $response->assertSee('format=csv');
        $response->assertDontSee('Total Inbound');
    }

    public function test_reports_page_renders_validation_errors(): void
    {
        $response = $this->from(route('reports.index'))->get(route('reports.show', [
            'from' => '2026-10-25',
            'to' => '2026-10-20',
        ]));

        $response->assertRedirect(route('reports.index'));
        $response->assertSessionHasErrors('date_to');

        $this->followingRedirects()
            ->from(route('reports.index'))
            ->get(route('reports.show', ['from' => '2026-10-25', 'to' => '2026-10-20']))
            ->assertSee('The end date must be greater than or equal to start date.');
    }
}
