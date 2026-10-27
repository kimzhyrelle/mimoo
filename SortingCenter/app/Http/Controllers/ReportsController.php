<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Parcel;
use App\Services\MetricsService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class ReportsController extends Controller
{
    public const TYPES = ReportService::TYPES;

    public function index(Request $request, ReportService $reportService, MetricsService $metrics)
    {
        return $this->show($request, $reportService, $metrics);
    }

    public function show(Request $request, ReportService $reportService, MetricsService $metrics)
    {
        // 1. Validation of request parameters
        $request->validate([
            'type' => 'nullable|string|in:parcels,delivery,riders,branches,overdue',
            'preset' => 'nullable|string|in:today,7d,7days,month',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'barangay' => 'nullable|string',
            'carrier' => 'nullable|string',
            'format' => 'nullable|string|in:csv,pdf,xlsx',
            'export' => 'nullable|string|in:csv,pdf,xlsx',
        ]);

        $type = in_array($request->get('type'), self::TYPES, true) ? $request->get('type') : 'parcels';
        $preset = $request->get('preset');
        $barangay = $request->get('barangay') ?: 'all';
        $carrier = $request->get('carrier') ?: 'all';
        $format = $request->get('format') ?? $request->get('export');

        // 2. Resolve date range with presets or explicit dates
        if ($preset === 'today') {
            $from = now()->startOfDay();
            $to = now()->endOfDay();
        } elseif ($preset === '7d' || $preset === '7days') {
            $from = now()->subDays(6)->startOfDay();
            $to = now()->endOfDay();
        } elseif ($preset === 'month') {
            $from = now()->startOfMonth()->startOfDay();
            $to = now()->endOfDay();
        } else {
            $rawFrom = $request->get('from') ?? $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
            $rawTo = $request->get('to') ?? $request->get('date_to', now()->format('Y-m-d'));

            $from = Carbon::parse($rawFrom)->startOfDay();
            $to = Carbon::parse($rawTo)->endOfDay();

            if ($to->lt($from)) {
                throw ValidationException::withMessages([
                    'date_to' => ['The end date must be greater than or equal to start date.'],
                ]);
            }
        }

        $dateFrom = $from->format('Y-m-d');
        $dateTo = $to->format('Y-m-d');

        // 3. Generate structured report via ReportService
        $report = $reportService->generate($type, $from, $to, $barangay, $carrier);

        // 4. Handle CSV and PDF exports
        if ($format === 'csv') {
            return $reportService->exportCsv($report, $type);
        }

        if ($format === 'xlsx') {
            return $reportService->exportXlsx($report, $type);
        }

        if ($format === 'pdf') {
            return $reportService->exportPdf($report, $type, $from, $to);
        }

        // 5. Handle JSON API requests
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json($report);
        }

        $areas = Area::orderBy('name')->get(['id', 'name']);
        $hasFilters = $barangay !== 'all' || $carrier !== 'all';
        $totalHandled = $hasFilters
            ? $this->filteredParcelCount($from, $to, $barangay, $carrier)
            : $metrics->parcelsTotalRangeValue($from, $to);
        $failed = (! $hasFilters && $from->isToday() && $to->isToday())
            ? $metrics->overdueCountValue()
            : Parcel::whereBetween('received_at', [$from, $to])
                ->when($barangay !== 'all', fn ($q) => $q->where('area_id', $barangay))
                ->when($carrier !== 'all', fn ($q) => $q->where('carrier', $carrier))
                ->overdue()
                ->count();

        $totalDelta = null;

        if ($type === 'parcels') {
            $daysCount = max(1, (int) $from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay()) + 1);
            $prevTo = (clone $from)->subDay()->endOfDay();
            $prevFrom = (clone $prevTo)->startOfDay()->subDays($daysCount - 1);
            $prevTotal = $this->filteredParcelCount($prevFrom, $prevTo, $barangay, $carrier);

            if ($prevTotal > 0) {
                $totalDelta = round((($totalHandled - $prevTotal) / $prevTotal) * 100, 1);
            }
        }

        return view('sorting.reports', compact(
            'report', 'type', 'barangay', 'carrier', 'dateFrom', 'dateTo', 'areas', 'totalDelta',
            'totalHandled', 'failed'
        ));
    }

    private function filteredParcelCount(Carbon $from, Carbon $to, string $barangay, string $carrier): int
    {
        return Parcel::whereBetween('received_at', [$from, $to])
            ->when($barangay !== 'all', fn ($q) => $q->where('area_id', $barangay))
            ->when($carrier !== 'all', fn ($q) => $q->where('carrier', $carrier))
            ->count();
    }
}
