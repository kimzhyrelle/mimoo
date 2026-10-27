<?php

namespace App\Services;

use App\Models\Area;
use App\Models\Parcel;
use App\Models\Rider;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportService
{
    public const TYPES = ['parcels', 'delivery', 'riders', 'branches', 'overdue'];

    public function __construct(
        protected MetricsService $metricsService
    ) {}

    /**
     * Generate structured report for a given report type.
     *
     * @return array{
     *     kpis: array<int, array{key: string, label: string, value: mixed, unit?: string, sub: string, tone?: string}>,
     *     rows: mixed,
     *     chart: array{labels: array<int, string>, values: array<int, mixed>},
     *     range_label: string
     * }
     */
    public function generate(string $type, Carbon $from, Carbon $to, ?string $barangay = 'all', ?string $carrier = 'all'): array
    {
        return match ($type) {
            'delivery' => $this->deliveryPerformance($from, $to, $barangay, $carrier),
            'riders' => $this->riderPerformance($from, $to, $barangay, $carrier),
            'branches' => $this->branchThroughput($from, $to, $barangay, $carrier),
            'overdue' => $this->overdueParcels($from, $to, $barangay, $carrier),
            default => $this->parcelsProcessed($from, $to, $barangay, $carrier),
        };
    }

    /**
     * 1. Parcels Processed Report
     */
    public function parcelsProcessed(Carbon $from, Carbon $to, ?string $barangay = 'all', ?string $carrier = 'all'): array
    {
        $parcels = $this->baseParcelQuery($from, $to, $barangay, $carrier)->get();
        $total = $parcels->count();

        // Daily series for table and chart
        $byDay = $parcels->groupBy(fn ($p) => $p->received_at?->format('Y-m-d') ?? '');
        $daily = [];
        $cursor = $to->copy()->startOfDay();
        $stop = $from->copy()->startOfDay();

        while ($cursor->gte($stop) && count($daily) < 31) {
            $dateStr = $cursor->format('Y-m-d');
            $dayParcels = $byDay->get($dateStr, collect());
            $daily[] = [
                'date' => $cursor->copy(),
                'processed' => $dayParcels->count(),
                'delivered' => $dayParcels->whereIn('status', ['delivered', 'buyer_confirmed'])->count(),
                'overdue' => $dayParcels->filter(fn ($p) => $p->isOverdue())->count(),
            ];
            $cursor->subDay();
        }

        $dailySorted = array_reverse($daily); // Oldest -> newest
        $chartSlice = array_slice($dailySorted, -7);

        $daysCount = max(1, (int) $from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay()) + 1);
        $dailyAvg = round($total / $daysCount, 1);

        $peakItem = collect($daily)->sortByDesc('processed')->first();
        $peakCount = $peakItem['processed'] ?? 0;
        $peakDate = ! empty($peakItem['date']) ? $peakItem['date']->format('M j') : '—';

        $sortedCount = $parcels->whereIn('status', ['sorted', 'assigned', 'out_for_delivery', 'delivered'])->count();
        $sortingRate = $total > 0 ? round(($sortedCount / $total) * 100, 1) : 100.0;

        $kpis = [
            [
                'key' => 'total_inbound',
                'label' => 'Total Inbound',
                'value' => number_format($total),
                'unit' => 'items',
                'sub' => 'Received at sorting hub',
                'tone' => 'primary',
            ],
            [
                'key' => 'daily_avg',
                'label' => 'Daily Average',
                'value' => $dailyAvg,
                'unit' => 'items/day',
                'sub' => 'Across '.$daysCount.' operating '.($daysCount === 1 ? 'day' : 'days'),
                'tone' => 'blue',
            ],
            [
                'key' => 'peak_volume',
                'label' => 'Peak Volume',
                'value' => $peakCount,
                'unit' => 'items',
                'sub' => "Peak recorded on {$peakDate}",
                'tone' => 'amber',
            ],
            [
                'key' => 'sorting_rate',
                'label' => 'Sorting Completion',
                'value' => "{$sortingRate}%",
                'unit' => 'sorted',
                'sub' => "{$sortedCount} processed past dock",
                'tone' => 'green',
            ],
        ];

        return [
            'type' => 'parcels',
            'title' => 'Parcels Processed',
            'kpis' => $kpis,
            'rows' => $dailySorted,
            'chart' => [
                'labels' => array_map(fn ($d) => $d['date']->format('M j'), $chartSlice),
                'values' => array_map(fn ($d) => $d['processed'], $chartSlice),
            ],
            'range_label' => $this->metricsService->formatRangeLabel($from, $to),
        ];
    }

    /**
     * 2. Delivery Performance Report
     */
    public function deliveryPerformance(Carbon $from, Carbon $to, ?string $barangay = 'all', ?string $carrier = 'all'): array
    {
        $parcels = $this->baseParcelQuery($from, $to, $barangay, $carrier)->get();
        $total = $parcels->count();

        $delivered = $parcels->whereIn('status', ['delivered', 'buyer_confirmed'])->count();
        $rate = $total > 0 ? round(($delivered / $total) * 100, 1) : 0.0;

        $deliveredParcelsWithTimes = $parcels->filter(fn ($p) => $p->assigned_at && $p->delivered_at);
        $avgMinutes = $deliveredParcelsWithTimes->isNotEmpty()
            ? (int) round($deliveredParcelsWithTimes->avg(fn ($p) => $p->assigned_at->diffInMinutes($p->delivered_at)))
            : 0;
        $avgDelLabel = $avgMinutes > 60 ? intdiv($avgMinutes, 60).'h '.($avgMinutes % 60).'m' : "{$avgMinutes}m";

        $confirmed = $parcels->whereNotNull('buyer_confirmed_at')->count();

        // Volume by Area
        $areas = $this->filteredAreas($from, $to, $barangay, $carrier);
        $volumeByArea = $areas->map(function ($a) {
            $cnt = $a->parcels->count();
            $done = $a->parcels->whereIn('status', ['delivered', 'buyer_confirmed'])->count();

            return [
                'name' => $a->name,
                'color' => $a->chart_color ?? '#1d4ed8',
                'count' => $cnt,
                'delivered' => $done,
                'overdue' => $a->parcels->filter(fn ($p) => $p->isOverdue())->count(),
                'rate' => $cnt > 0 ? round(($done / $cnt) * 100, 1) : 0.0,
            ];
        })->sortByDesc('count')->values();

        $topAreas = $volumeByArea->take(7);

        $kpis = [
            [
                'key' => 'delivered_count',
                'label' => 'Delivered Packages',
                'value' => number_format($delivered),
                'unit' => 'verified',
                'sub' => 'Successfully handed over',
                'tone' => 'green',
            ],
            [
                'key' => 'delivery_rate',
                'label' => 'Delivery Success Rate',
                'value' => "{$rate}%",
                'unit' => 'completion',
                'sub' => 'Target benchmark: 85%',
                'tone' => 'primary',
            ],
            [
                'key' => 'avg_delivery_time',
                'label' => 'Avg Delivery Time',
                'value' => $avgDelLabel,
                'unit' => 'duration',
                'sub' => 'From dispatch to doorstep',
                'tone' => 'blue',
            ],
            [
                'key' => 'buyer_confirmed',
                'label' => 'Buyer Confirmed',
                'value' => number_format($confirmed),
                'unit' => 'validated',
                'sub' => 'QR handshake confirmed',
                'tone' => 'green',
            ],
        ];

        return [
            'type' => 'delivery',
            'title' => 'Delivery Performance',
            'kpis' => $kpis,
            'rows' => $volumeByArea,
            'chart' => [
                'labels' => $topAreas->pluck('name')->all(),
                'values' => $topAreas->pluck('rate')->all(),
            ],
            'range_label' => $this->metricsService->formatRangeLabel($from, $to),
        ];
    }

    /**
     * 3. Rider Performance Report
     */
    public function riderPerformance(Carbon $from, Carbon $to, ?string $barangay = 'all', ?string $carrier = 'all'): array
    {
        $riders = Rider::query()
            ->when($barangay && $barangay !== 'all', fn ($q) => $q->where('area_id', $barangay))
            ->with(['area', 'parcels' => function ($q) use ($from, $to, $carrier) {
                $q->whereBetween('received_at', [$from, $to]);

                if ($carrier && $carrier !== 'all') {
                    $q->where('carrier', $carrier);
                }
            }])->get()
            ->map(function ($r) {
                $rParcels = $r->parcels;
                $total = $rParcels->count();
                $done = $rParcels->whereIn('status', ['delivered', 'buyer_confirmed'])->count();
                $avgDel = $rParcels->filter(fn ($p) => $p->assigned_at && $p->delivered_at)
                    ->avg(fn ($p) => $p->assigned_at->diffInMinutes($p->delivered_at)) ?? 0;

                return [
                    'rider' => $r,
                    'area' => $r->area?->name ?? '—',
                    'total' => $total,
                    'delivered' => $done,
                    'success_pct' => $total ? round(($done / $total) * 100, 1) : 0.0,
                    'avg_del_min' => (int) round($avgDel),
                ];
            })->sortByDesc('total')->values();

        $activeRiders = $riders->where('total', '>', 0);
        $activeCouriersCount = $activeRiders->count();
        $fleetDelivered = $riders->sum('delivered');
        $avgPerRider = $activeCouriersCount > 0 ? round($fleetDelivered / $activeCouriersCount, 1) : 0.0;
        $totalHandled = $riders->sum('total');
        $fleetSuccessPct = $totalHandled > 0 ? round(($fleetDelivered / $totalHandled) * 100, 1) : 0.0;

        $topRiders = $riders->take(7);

        $kpis = [
            [
                'key' => 'active_couriers',
                'label' => 'Active Couriers',
                'value' => $activeCouriersCount,
                'unit' => 'riders',
                'sub' => 'Handled runs in period',
                'tone' => 'primary',
            ],
            [
                'key' => 'fleet_deliveries',
                'label' => 'Fleet Deliveries',
                'value' => number_format($fleetDelivered),
                'unit' => 'completed',
                'sub' => 'Total doorstep completions',
                'tone' => 'green',
            ],
            [
                'key' => 'avg_per_rider',
                'label' => 'Avg Deliveries / Rider',
                'value' => $avgPerRider,
                'unit' => 'parcels/courier',
                'sub' => 'Workload efficiency',
                'tone' => 'blue',
            ],
            [
                'key' => 'fleet_success_pct',
                'label' => 'Fleet Success Rate',
                'value' => "{$fleetSuccessPct}%",
                'unit' => 'success',
                'sub' => 'Overall fleet completion',
                'tone' => 'primary',
            ],
        ];

        return [
            'type' => 'riders',
            'title' => 'Rider Performance',
            'kpis' => $kpis,
            'rows' => $riders,
            'chart' => [
                'labels' => $topRiders->map(fn ($r) => $r['rider']->name)->all(),
                'values' => $topRiders->pluck('total')->all(),
            ],
            'range_label' => $this->metricsService->formatRangeLabel($from, $to),
        ];
    }

    /**
     * 4. Branch Throughput Report
     */
    public function branchThroughput(Carbon $from, Carbon $to, ?string $barangay = 'all', ?string $carrier = 'all'): array
    {
        $areas = $this->filteredAreas($from, $to, $barangay, $carrier);
        $totalThroughput = $this->baseParcelQuery($from, $to, $barangay, $carrier)->count();

        $volumeByArea = $areas->map(function ($a) use ($totalThroughput) {
            $cnt = $a->parcels->count();
            $done = $a->parcels->whereIn('status', ['delivered', 'buyer_confirmed'])->count();
            $share = $totalThroughput > 0 ? round(($cnt / $totalThroughput) * 100, 1) : 0.0;

            return [
                'name' => $a->name,
                'color' => $a->chart_color ?? '#1d4ed8',
                'count' => $cnt,
                'delivered' => $done,
                'overdue' => $a->parcels->filter(fn ($p) => $p->isOverdue())->count(),
                'rate' => $cnt > 0 ? round(($done / $cnt) * 100, 1) : 0.0,
                'share_pct' => $share,
            ];
        })->sortByDesc('count')->values();

        $activeZonesCount = $volumeByArea->where('count', '>', 0)->count();
        $peakZone = $volumeByArea->first();
        $peakSharePct = $peakZone['share_pct'] ?? 0.0;
        $peakZoneName = $peakZone['name'] ?? 'None';

        // Average hub processing time (received to sorted)
        $sortedParcels = $this->baseParcelQuery($from, $to, $barangay, $carrier)
            ->whereNotNull('received_at')
            ->whereNotNull('sorted_at')
            ->get();
        $avgSortMins = $sortedParcels->isNotEmpty()
            ? (int) round($sortedParcels->avg(fn ($p) => $p->received_at->diffInMinutes($p->sorted_at)))
            : 0;
        $avgHubTimeLabel = $avgSortMins > 60 ? intdiv($avgSortMins, 60).'h '.($avgSortMins % 60).'m' : "{$avgSortMins}m";

        $topZones = $volumeByArea->take(7);

        $kpis = [
            [
                'key' => 'hub_throughput',
                'label' => 'Hub Throughput',
                'value' => number_format($totalThroughput),
                'unit' => 'items',
                'sub' => 'Total volume sorted & staged',
                'tone' => 'primary',
            ],
            [
                'key' => 'active_zones',
                'label' => 'Active Sorting Zones',
                'value' => $activeZonesCount,
                'unit' => 'barangays',
                'sub' => 'Receiving delivery volume',
                'tone' => 'blue',
            ],
            [
                'key' => 'peak_cluster_share',
                'label' => 'Peak Cluster Share',
                'value' => "{$peakSharePct}%",
                'unit' => 'concentration',
                'sub' => "Highest: {$peakZoneName}",
                'tone' => 'amber',
            ],
            [
                'key' => 'avg_hub_time',
                'label' => 'Avg Hub Turnaround',
                'value' => $avgHubTimeLabel,
                'unit' => 'duration',
                'sub' => 'Intake to staging bin',
                'tone' => 'primary',
            ],
        ];

        return [
            'type' => 'branches',
            'title' => 'Branch Throughput',
            'kpis' => $kpis,
            'rows' => $volumeByArea,
            'chart' => [
                'labels' => $topZones->pluck('name')->all(),
                'values' => $topZones->pluck('count')->all(),
            ],
            'range_label' => $this->metricsService->formatRangeLabel($from, $to),
        ];
    }

    /**
     * 5. Overdue Parcels Report
     */
    public function overdueParcels(Carbon $from, Carbon $to, ?string $barangay = 'all', ?string $carrier = 'all'): array
    {
        $base = $this->baseParcelQuery($from, $to, $barangay, $carrier);
        $totalHandled = (clone $base)->count();

        $overdueParcels = (clone $base)->overdue()->with(['area', 'rider'])->orderBy('received_at')->get();
        $overdueCount = $overdueParcels->count();

        $slaBreachRate = $totalHandled > 0 ? round(($overdueCount / $totalHandled) * 100, 1) : 0.0;

        $avgOverdueMins = $overdueParcels->isNotEmpty()
            ? (int) round($overdueParcels->avg(fn ($p) => $p->overdueMinutes()))
            : 0;
        $avgDelayLabel = $avgOverdueMins > 60 ? intdiv($avgOverdueMins, 60).'h '.($avgOverdueMins % 60).'m' : "{$avgOverdueMins}m";

        $pendingReassign = $overdueParcels->whereIn('status', ['assigned', 'out_for_delivery', 'failed'])->count();

        // Chart: Overdue by Area
        $byArea = $overdueParcels->groupBy(fn ($p) => $p->area?->name ?? 'Unassigned')->map->count()->sortDesc();
        $topAreas = $byArea->take(7);

        $kpis = [
            [
                'key' => 'overdue_exceptions',
                'label' => 'Overdue Exceptions',
                'value' => $overdueCount,
                'unit' => 'exceptions',
                'sub' => 'Breached SLA threshold',
                'tone' => 'destructive',
            ],
            [
                'key' => 'sla_breach_rate',
                'label' => 'SLA Breach Rate',
                'value' => "{$slaBreachRate}%",
                'unit' => 'of volume',
                'sub' => 'Parcels delayed beyond SLA',
                'tone' => 'destructive',
            ],
            [
                'key' => 'avg_sla_delay',
                'label' => 'Avg SLA Delay',
                'value' => $avgDelayLabel,
                'unit' => 'delay',
                'sub' => 'Average past due time',
                'tone' => 'amber',
            ],
            [
                'key' => 'pending_reassign',
                'label' => 'Pending Reassignment',
                'value' => $pendingReassign,
                'unit' => 'parcels',
                'sub' => 'Held by courier or stalled',
                'tone' => 'destructive',
            ],
        ];

        return [
            'type' => 'overdue',
            'title' => 'Overdue Parcels',
            'kpis' => $kpis,
            'rows' => $overdueParcels,
            'chart' => [
                'labels' => $topAreas->keys()->all(),
                'values' => $topAreas->values()->all(),
            ],
            'range_label' => $this->metricsService->formatRangeLabel($from, $to),
        ];
    }

    /**
     * Export report data as CSV streamed response.
     */
    public function exportCsv(array $report, string $type): StreamedResponse
    {
        $filename = 'mimoo-report-'.$type.'-'.now()->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($report, $type) {
            $out = fopen('php://output', 'w');

            // Metadata / KPIs block
            fputcsv($out, ['# Mimoo Sorting Center Report: '.($report['title'] ?? ucfirst($type))]);
            fputcsv($out, ['# Date Range: '.($report['range_label'] ?? 'All')]);
            fputcsv($out, []);
            fputcsv($out, ['--- KPI SUMMARY ---']);
            foreach ($report['kpis'] ?? [] as $kpi) {
                fputcsv($out, [$kpi['label'], $kpi['value'], $kpi['unit'] ?? '', $kpi['sub'] ?? '']);
            }
            fputcsv($out, []);
            fputcsv($out, ['--- DATA TABLE ---']);

            match ($type) {
                'delivery', 'branches' => (function () use ($out, $report, $type) {
                    if ($type === 'branches') {
                        fputcsv($out, ['Barangay', 'Parcels', 'Delivered', 'Overdue', 'Share %']);
                        foreach ($report['rows'] ?? [] as $r) {
                            fputcsv($out, [$r['name'], $r['count'], $r['delivered'], $r['overdue'], $r['share_pct'].'%']);
                        }
                    } else {
                        fputcsv($out, ['Barangay', 'Parcels', 'Delivered', 'Overdue', 'Delivery rate %']);
                        foreach ($report['rows'] ?? [] as $r) {
                            fputcsv($out, [$r['name'], $r['count'], $r['delivered'], $r['overdue'], $r['rate'].'%']);
                        }
                    }
                })(),
                'riders' => (function () use ($out, $report) {
                    fputcsv($out, ['Rider', 'Barangay', 'Parcels', 'Delivered', 'Success %', 'Avg delivery (min)']);
                    foreach ($report['rows'] ?? [] as $r) {
                        fputcsv($out, [
                            $r['rider']->name ?? '—',
                            $r['area'] ?? '—',
                            $r['total'],
                            $r['delivered'],
                            $r['success_pct'].'%',
                            $r['avg_del_min'],
                        ]);
                    }
                })(),
                'overdue' => (function () use ($out, $report) {
                    fputcsv($out, ['Parcel', 'Barangay', 'Rider', 'Received', 'Overdue Time', 'Status']);
                    foreach ($report['rows'] ?? [] as $p) {
                        fputcsv($out, [
                            $p->tracking_no,
                            $p->area?->name ?? '—',
                            $p->rider?->name ?? '—',
                            $p->received_at?->format('Y-m-d H:i') ?? '—',
                            $p->overdueLabel(),
                            $p->statusLabel(),
                        ]);
                    }
                })(),
                default => (function () use ($out, $report) {
                    fputcsv($out, ['Date', 'Parcels processed', 'Delivered', 'Overdue']);
                    foreach ($report['rows'] ?? [] as $d) {
                        fputcsv($out, [
                            $d['date'] instanceof Carbon ? $d['date']->format('Y-m-d') : (string) $d['date'],
                            $d['processed'],
                            $d['delivered'],
                            $d['overdue'],
                        ]);
                    }
                })(),
            };

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * Export the same rows and KPI summary as the screen/CSV, as a real XLSX
     * workbook (dependency-free, built with ZipArchive).
     */
    public function exportXlsx(array $report, string $type): BinaryFileResponse
    {
        $filename = 'mimoo-report-'.$type.'-'.now()->format('Ymd').'.xlsx';
        $temp = tempnam(sys_get_temp_dir(), 'mimoo-xlsx-');
        $zip = new \ZipArchive;
        $zip->open($temp, \ZipArchive::OVERWRITE);

        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'</Types>');

        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>');

        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="'.htmlspecialchars(substr($report['title'] ?? ucfirst($type), 0, 31), ENT_XML1).'" sheetId="1" r:id="rId1"/></sheets>'
            .'</workbook>');

        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'</Relationships>');

        $rows = $this->exportRows($report, $type);
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';

        foreach ($rows as $rowIndex => $row) {
            $xml .= '<row r="'.($rowIndex + 1).'">';
            foreach (array_values($row) as $columnIndex => $value) {
                $ref = $this->columnLetter($columnIndex + 1).($rowIndex + 1);
                if (is_int($value) || is_float($value)) {
                    $xml .= '<c r="'.$ref.'"><v>'.$value.'</v></c>';
                } else {
                    $xml .= '<c r="'.$ref.'" t="inlineStr"><is><t xml:space="preserve">'
                        .htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</t></is></c>';
                }
            }
            $xml .= '</row>';
        }

        $xml .= '</sheetData></worksheet>';
        $zip->addFromString('xl/worksheets/sheet1.xml', $xml);
        $zip->close();

        return response()->download($temp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * @return array<int, array<int, string|int|float>>
     */
    public function exportRows(array $report, string $type): array
    {
        $rows = [
            ['Mimoo Sorting Center Report: '.($report['title'] ?? ucfirst($type))],
            ['Date Range: '.($report['range_label'] ?? 'All')],
            [],
            ['KPI SUMMARY'],
        ];

        foreach ($report['kpis'] ?? [] as $kpi) {
            $rows[] = [$kpi['label'], (string) $kpi['value'], $kpi['unit'] ?? '', $kpi['sub'] ?? ''];
        }

        $rows[] = [];
        $rows[] = ['DATA TABLE'];

        return array_merge($rows, $this->exportTableRows($report, $type));
    }

    /**
     * @return array<int, array<int, string|int|float>>
     */
    private function exportTableRows(array $report, string $type): array
    {
        return match ($type) {
            'delivery' => array_merge(
                [['Barangay', 'Parcels', 'Delivered', 'Overdue', 'Delivery rate %']],
                collect($report['rows'] ?? [])->map(fn ($r) => [$r['name'], $r['count'], $r['delivered'], $r['overdue'], $r['rate'].'%'])->all(),
            ),
            'branches' => array_merge(
                [['Barangay', 'Parcels', 'Delivered', 'Overdue', 'Share %']],
                collect($report['rows'] ?? [])->map(fn ($r) => [$r['name'], $r['count'], $r['delivered'], $r['overdue'], $r['share_pct'].'%'])->all(),
            ),
            'riders' => array_merge(
                [['Rider', 'Barangay', 'Parcels', 'Delivered', 'Success %', 'Avg delivery (min)']],
                collect($report['rows'] ?? [])->map(fn ($r) => [
                    $r['rider']->name ?? '-', $r['area'] ?? '-', $r['total'], $r['delivered'], $r['success_pct'].'%', $r['avg_del_min'],
                ])->all(),
            ),
            'overdue' => array_merge(
                [['Parcel', 'Barangay', 'Rider', 'Received', 'Overdue Time', 'Status']],
                collect($report['rows'] ?? [])->map(fn ($p) => [
                    $p->tracking_no, $p->area?->name ?? '-', $p->rider?->name ?? '-',
                    $p->received_at?->format('Y-m-d H:i') ?? '-', $p->overdueLabel(), $p->statusLabel(),
                ])->all(),
            ),
            default => array_merge(
                [['Date', 'Parcels processed', 'Delivered', 'Overdue']],
                collect($report['rows'] ?? [])->map(fn ($d) => [
                    $d['date'] instanceof Carbon ? $d['date']->format('Y-m-d') : (string) $d['date'],
                    $d['processed'], $d['delivered'], $d['overdue'],
                ])->all(),
            ),
        };
    }

    private function columnLetter(int $index): string
    {
        $letters = '';

        while ($index > 0) {
            $index--;
            $letters = chr(65 + ($index % 26)).$letters;
            $index = intdiv($index, 26);
        }

        return $letters;
    }

    /**
     * Export report data as PDF response.
     */
    public function exportPdf(array $report, string $type, Carbon $from, Carbon $to)
    {
        $filename = 'mimoo-report-'.$type.'-'.now()->format('Ymd').'.pdf';

        $pdf = Pdf::loadView('pdf.report', [
            'report' => $report,
            'type' => $type,
            'from' => $from,
            'to' => $to,
        ])->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

    /**
     * Areas with their parcels constrained to the report range and filters.
     */
    protected function filteredAreas(Carbon $from, Carbon $to, ?string $barangay = 'all', ?string $carrier = 'all')
    {
        return Area::query()
            ->when($barangay && $barangay !== 'all', fn ($q) => $q->whereKey($barangay))
            ->with(['parcels' => function ($q) use ($from, $to, $carrier) {
                $q->whereBetween('received_at', [$from, $to]);

                if ($carrier && $carrier !== 'all') {
                    $q->where('carrier', $carrier);
                }
            }])
            ->orderBy('name')
            ->get();
    }

    /**
     * Helper base parcel query with standard filters.
     */
    protected function baseParcelQuery(Carbon $from, Carbon $to, ?string $barangay = 'all', ?string $carrier = 'all')
    {
        return Parcel::whereBetween('received_at', [$from, $to])
            ->when($barangay && $barangay !== 'all', fn ($q) => $q->where('area_id', $barangay))
            ->when($carrier && $carrier !== 'all', fn ($q) => $q->where('carrier', $carrier));
    }
}
