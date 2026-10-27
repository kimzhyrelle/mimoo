<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListFilterRequest;
use App\Models\Area;
use App\Models\Parcel;
use App\Models\Rider;
use App\Services\AssignmentService;
use App\Services\MetricsService;
use App\Services\ParcelStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SortParcelsController extends Controller
{
    public function index(ListFilterRequest $request, MetricsService $metrics)
    {
        $filters = $request->validated();
        $q = $filters['q'] ?? null;
        $areaId = $filters['area_id'] ?? null;

        $parcels = Parcel::with(['area', 'rider'])
            ->whereIn('status', ['awaiting_sort', 'sorted', 'assigned'])
            ->listFilters($filters)
            ->orderBy('received_at')
            ->get();

        $areas = Area::with('rider')->orderBy('name')->get();

        // One "cluster" per barangay that has sorted / assigned parcels
        $byArea = Parcel::whereIn('status', ['sorted', 'assigned'])->whereNotNull('area_id')->get()->groupBy('area_id');
        $clusters = $areas->map(function (Area $a) use ($byArea) {
            $ps = $byArea->get($a->id, collect());

            return (object) [
                'area' => $a,
                'count' => $ps->count(),
                'pending' => $ps->where('status', 'sorted')->count(),
                'assigned' => $ps->where('status', 'assigned')->count(),
                'weight' => (float) $ps->sum('weight_kg'),
                'oldest' => $ps->min('received_at'),
            ];
        })->filter(fn ($c) => $c->count > 0);

        $clusterSort = $request->query('cluster_sort', 'heaviest');
        $clusters = ($clusterSort === 'oldest'
            ? $clusters->sortBy(fn ($c) => $c->oldest?->timestamp ?? PHP_INT_MAX)
            : $clusters->sortByDesc('count'))->values();

        // Real computed assigned clusters count for the banner
        $assignedClusters = $clusters->filter(fn ($c) => $c->area->rider && $c->area->rider->isActive())->count();
        $dispatchableParcels = $clusters
            ->filter(fn ($c) => $c->area->rider && $c->area->rider->isActive())
            ->sum('pending');

        return view('sorting.sort-parcels', [
            'clusters' => $clusters,
            'assignedClusters' => $assignedClusters,
            'dispatchableParcels' => $dispatchableParcels,
            'clusterSort' => $clusterSort,
            'riders' => Rider::with('area')->orderBy('name')->get(),
            'stagedParcels' => $metrics->stagedParcelsValue(),
            'weighedCount' => Parcel::where('status', 'sorted')->whereNotNull('weight_kg')->count(),
            'parcels' => $parcels,
            'areas' => $areas,
            'allAreas' => $areas,
            'awaitingSort' => $metrics->parcelsAwaitingSortValue(),
            'areaDetermined' => $metrics->stagedParcelsValue(),
            'assignedToday' => $metrics->assignedTodayValue(),
            'ridersOnShift' => $metrics->ridersOnDutyValue(),
            'queueVolume' => $metrics->parcelsInQueueValue(),
            'idleRiders' => $metrics->ridersIdleValue(),
            'busyRiders' => $metrics->ridersInRouteValue(),
            'q' => $q,
            'area_id' => $areaId,
        ]);
    }

    // Match the parcel's address text against the hub's known barangays.
    public function determineArea(Parcel $parcel, ParcelStatusService $statusService)
    {
        if ($parcel->isDelivered()) {
            return back()->withErrors('Delivered parcels cannot be modified.');
        }

        $area = Area::matchForAddress($parcel->address);
        if ($area) {
            $parcel->update(['area_id' => $area->id]);
        }

        if ($parcel->status !== 'sorted') {
            $statusService->transition($parcel, 'sorted', 'Address matched to barangay');
        }

        $message = $area
            ? "{$parcel->tracking_no} sorted to {$area->name}."
            : "{$parcel->tracking_no} could not be matched to a barangay — set it manually.";

        return back()->with('status', $message)->with('toast', $message);
    }

    public function assignRider(Request $request, Parcel $parcel, AssignmentService $assignmentService): JsonResponse|RedirectResponse
    {
        if ($parcel->isDelivered()) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => 'Delivered parcels cannot be reassigned.'], 422);
            }

            return back()->withErrors('Delivered parcels cannot be reassigned.');
        }

        if (! $parcel->area_id) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => 'Determine the barangay before assigning a rider.'], 422);
            }

            return back()->withErrors('Determine the barangay before assigning a rider.');
        }

        $riderId = $request->input('rider_id') ?? $parcel->area?->rider_id;

        if (! $riderId) {
            $msg = "No rider is assigned to {$parcel->area->name} yet — assign one in Rider Management first.";
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => $msg], 422);
            }

            return back()->withErrors($msg);
        }

        try {
            $result = $assignmentService->assignParcel($parcel, (int) $riderId, $request->user());

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json($result);
            }

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
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
            }

            return back()->withErrors($e->errors());
        }
    }

    public function setCarrier(Request $request, Parcel $parcel)
    {
        if ($parcel->isDelivered()) {
            return back()->withErrors('Delivered parcels cannot be modified.');
        }

        $request->validate(['carrier' => 'required|in:jnt,lbc,lalamove']);
        $parcel->update(['carrier' => $request->carrier]);

        $message = "Carrier updated for {$parcel->tracking_no}.";

        return back()->with('status', $message)->with('toast', $message);
    }
}
