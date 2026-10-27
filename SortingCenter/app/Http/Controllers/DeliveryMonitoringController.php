<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListFilterRequest;
use App\Models\Area;
use App\Models\Parcel;
use App\Models\Rider;
use App\Services\MetricsService;
use App\Services\ParcelStatusService;
use Illuminate\Validation\ValidationException;

class DeliveryMonitoringController extends Controller
{
    public function index(ListFilterRequest $request, MetricsService $metrics)
    {
        $filters = $request->validated();
        $filter = $filters['filter'] ?? 'all';
        $q = $filters['q'] ?? null;
        $areaId = $filters['area_id'] ?? null;

        $query = ($filter === 'archived')
            ? Parcel::onlyTrashed()->with(['area', 'rider'])
            : Parcel::with(['area', 'rider'])->whereIn('status', ['assigned', 'out_for_delivery', 'delivered', 'failed']);

        $parcels = $query
            ->when($filter === 'failed', fn ($qBuilder) => $qBuilder->overdue())
            ->when($filter !== 'all' && $filter !== 'archived' && $filter !== 'failed', fn ($qBuilder) => $qBuilder->where('status', match ($filter) {
                'assigned' => 'assigned',
                'out' => 'out_for_delivery',
                'delivered' => 'delivered',
                default => $filter,
            }))
            ->listFilters($filters, 'assigned_at')
            ->orderByDesc('assigned_at')
            ->get();

        $rail = $metrics->deliveryRailCounts();

        $selected = $request->query('parcel')
            ? (($filter === 'archived' ? Parcel::withTrashed() : Parcel::query())->with(['area', 'rider'])->find($request->query('parcel')))
            : null;
        $selected ??= $parcels->first();
        $events = $selected ? $selected->custodyEvents() : collect();

        $counts = $metrics->deliveryMonitoringCounts();
        $counts['archived'] = Parcel::onlyTrashed()->count();
        $activeFleet = $metrics->ridersOnDutyValue();

        return view('sorting.delivery-monitoring', [
            'selected' => $selected,
            'events' => $events,
            'counts' => $counts,
            'activeFleet' => $activeFleet,
            'parcels' => $parcels,
            'rail' => $rail,
            'filter' => $filter,
            'q' => $q,
            'area_id' => $areaId,
            'allAreas' => Area::orderBy('name')->get(),
            'riders' => Rider::orderBy('name')->get(),
        ]);
    }

    // assigned -> out_for_delivery -> delivered
    public function advance(Parcel $parcel, ParcelStatusService $statusService)
    {
        if ($parcel->isDelivered()) {
            return back()->withErrors('Delivered parcels are locked.');
        }

        $nextStatus = match ($parcel->status) {
            'assigned' => 'out_for_delivery',
            'out_for_delivery' => 'delivered',
            default => null,
        };

        if (! $nextStatus) {
            return back()->withErrors("Cannot advance parcel from status {$parcel->status}.");
        }

        try {
            $result = $statusService->transition($parcel, $nextStatus);

            $redirect = back()
                ->with('status', $result['message'])
                ->with('toast', $result['message']);

            if (! empty($result['undo_token'])) {
                $redirect
                    ->with('toast_undo', 'Undo')
                    ->with('toast_undo_url', route('shipments.undo', ['token' => $result['undo_token']]));
            }

            return $redirect;
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }

    // The rider scans the parcel at end of day to confirm it wasn't delivered.
    public function scanUndelivered(Parcel $parcel, ParcelStatusService $statusService)
    {
        if ($parcel->isDelivered()) {
            return back()->withErrors('Delivered parcels are locked.');
        }

        try {
            $result = $statusService->transition($parcel, 'failed', 'Reschedule / return to hub');

            $redirect = back()
                ->with('status', $result['message'])
                ->with('toast', $result['message']);

            if (! empty($result['undo_token'])) {
                $redirect
                    ->with('toast_undo', 'Undo')
                    ->with('toast_undo_url', route('shipments.undo', ['token' => $result['undo_token']]));
            }

            return $redirect;
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }
}
