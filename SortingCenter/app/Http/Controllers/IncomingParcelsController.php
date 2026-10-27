<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListFilterRequest;
use App\Models\Area;
use App\Models\Parcel;
use App\Services\MetricsService;
use App\Services\ParcelStatusService;
use Illuminate\Support\Carbon;

class IncomingParcelsController extends Controller
{
    public function index(ListFilterRequest $request, MetricsService $metrics)
    {
        $filters = $request->validated();
        $q = $filters['q'] ?? null;
        $areaId = $filters['area_id'] ?? null;

        $parcels = Parcel::with(['droppedOffBy', 'area'])
            ->where('status', 'dropped_off')
            ->listFilters($filters)
            ->orderBy('received_at')
            ->get();

        $scannedToday = $metrics->parcelsScannedTodayValue();

        $areas = Area::with('rider')
            ->whereNotNull('pickup_window')
            ->get()
            ->sortBy(function ($a) {
                try {
                    return $a->cutoff_time ? Carbon::parse($a->cutoff_time)->format('H:i') : '99:99';
                } catch (\Throwable) {
                    return $a->cutoff_time;
                }
            })
            ->values()
            ->map(function ($a) {
                $a->has_no_courier = ! $a->rider || ! $a->rider->isActive();

                return $a;
            });

        // Parcel shown in the "Scanned Result" panel: manual entry, a clicked row, or the next in the queue
        $notFound = null;
        $selected = null;
        if ($tracking = trim((string) $request->query('tracking'))) {
            $selected = Parcel::with(['droppedOffBy', 'area'])->where('tracking_no', $tracking)->first()
                ?? Parcel::with(['droppedOffBy', 'area'])->where('tracking_no', '#'.ltrim($tracking, '#'))->first();
            $notFound = $selected ? null : $tracking;
        } elseif ($id = $request->query('parcel')) {
            $selected = Parcel::with(['droppedOffBy', 'area'])->find($id);
        }
        if (! $selected && ! $notFound) {
            $selected = $parcels->first();
        }

        $recentScanned = Parcel::with('area')
            ->where('status', '!=', 'dropped_off')
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        $shiftRate = $metrics->incomingShiftRate();

        return view('sorting.incoming-parcels', [
            'selected' => $selected,
            'notFound' => $notFound,
            'targetArea' => $selected ? ($selected->area ?? Area::matchForAddress($selected->address)) : null,
            'recentScanned' => $recentScanned,
            'shiftRate' => $shiftRate,
            'parcels' => $parcels,
            'awaitingScan' => $metrics->parcelsAwaitingScanValue(),
            'scannedToday' => $scannedToday,
            'ridersDroppingOff' => $metrics->ridersDroppingOffValue(),
            'areas' => $areas,
            'allAreas' => Area::orderBy('name')->get(),
            'q' => $q,
            'area_id' => $areaId,
        ]);
    }

    public function scan(Parcel $parcel, ParcelStatusService $statusService)
    {
        if ($parcel->isDelivered()) {
            return back()->withErrors('Delivered parcels cannot be scanned.');
        }

        $result = $statusService->transition($parcel, 'awaiting_sort', 'Scanned at inbound station');

        $redirect = back()
            ->with('status', "{$parcel->tracking_no} scanned.")
            ->with('toast', "{$parcel->tracking_no} scanned.");

        if (! empty($result['undo_token'])) {
            $redirect
                ->with('toast_undo', 'Undo')
                ->with('toast_undo_url', route('shipments.undo', ['token' => $result['undo_token']]));
        }

        return $redirect;
    }

    public function scanAll(ParcelStatusService $statusService)
    {
        $parcels = Parcel::where('status', 'dropped_off')->get();

        foreach ($parcels as $parcel) {
            $statusService->transition($parcel, 'awaiting_sort', 'Bulk scanned at inbound station');
        }

        return back()->with('status', $parcels->count().' parcels scanned.')->with('toast', $parcels->count().' parcels scanned.');
    }
}
