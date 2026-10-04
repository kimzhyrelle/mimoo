<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Parcel;
use App\Models\Rider;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $barangay  = $request->get('barangay', 'all');
        $carrier   = $request->get('carrier', 'all');
        $dateFrom  = $request->get('date_from', now()->startOfWeek()->format('Y-m-d'));
        $dateTo    = $request->get('date_to',   now()->format('Y-m-d'));

        $from = Carbon::parse($dateFrom)->startOfDay();
        $to   = Carbon::parse($dateTo)->endOfDay();

        // Base query scoped to date range
        $base = Parcel::whereBetween('received_at', [$from, $to]);
        if ($barangay !== 'all') $base->where('area_id', $barangay);
        if ($carrier  !== 'all') $base->where('carrier', $carrier);

        $parcels        = (clone $base)->get();
        $totalHandled   = $parcels->count();
        $delivered      = $parcels->whereIn('status', ['delivered','buyer_confirmed'])->count();
        $failed         = $parcels->where('status','failed')->count();
        $onTimeRate     = $totalHandled ? round(($delivered / max($totalHandled,1)) * 100, 1) : 0;

        // Avg sort-to-assign time (minutes)
        $avgSortTime = $parcels->filter(fn($p) => $p->sorted_at && $p->assigned_at)
            ->avg(fn($p) => $p->sorted_at->diffInMinutes($p->assigned_at)) ?? 0;
        $avgSortH   = floor($avgSortTime / 60);
        $avgSortM   = (int)($avgSortTime % 60);

        // Volume by barangay for chart
        $areas = Area::with(['parcels' => fn($q) => $q->whereBetween('received_at', [$from, $to])])->get();
        $volumeByArea = $areas->map(fn($a) => [
            'name'  => $a->name,
            'color' => $a->chart_color ?? '#2A1B4D',
            'count' => $a->parcels->count(),
        ])->sortByDesc('count')->values();

        // Carrier mix
        $carrierMix = $parcels->groupBy('carrier')->map->count()->sortDesc();

        // Rider performance
        $riders = Rider::with(['area', 'parcels' => fn($q) => $q->whereBetween('received_at', [$from, $to])])->get()
            ->map(function ($r) {
                $rParcels   = $r->parcels;
                $total      = $rParcels->count();
                $done       = $rParcels->whereIn('status',['delivered','buyer_confirmed'])->count();
                $successPct = $total ? round(($done / $total) * 100) : 0;
                $avgDel     = $rParcels->filter(fn($p) => $p->assigned_at && $p->delivered_at)
                    ->avg(fn($p) => $p->assigned_at->diffInMinutes($p->delivered_at)) ?? 0;
                return [
                    'rider'       => $r,
                    'area'        => $r->area?->name ?? '—',
                    'total'       => $total,
                    'delivered'   => $done,
                    'success_pct' => $successPct,
                    'avg_del_min' => (int) $avgDel,
                ];
            })->sortByDesc('total')->values();

        // Previous period for delta
        $prevFrom   = (clone $from)->subDays($to->diffInDays($from) + 1);
        $prevTo     = (clone $from)->subDay()->endOfDay();
        $prevTotal  = Parcel::whereBetween('received_at', [$prevFrom, $prevTo])->count();
        $totalDelta = $prevTotal ? round((($totalHandled - $prevTotal) / $prevTotal) * 100, 1) : null;

        return view('sorting.reports', compact(
            'totalHandled','onTimeRate','avgSortH','avgSortM','failed',
            'volumeByArea','carrierMix','riders',
            'barangay','carrier','dateFrom','dateTo',
            'areas','totalDelta'
        ));
    }
}
