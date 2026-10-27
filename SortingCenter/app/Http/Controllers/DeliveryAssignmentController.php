<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkAssignRequest;
use App\Models\Activity;
use App\Models\Area;
use App\Models\DispatchManifest;
use App\Models\Parcel;
use App\Models\ParcelStatusLog;
use App\Models\ParcelWeighIn;
use App\Services\AssignmentService;
use App\Services\MetricsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DeliveryAssignmentController extends Controller
{
    public function index(MetricsService $metrics)
    {
        // All areas with their rider and sorted-but-unassigned parcels
        $areas = Area::with([
            'rider',
            'parcels' => fn ($q) => $q->where('status', 'sorted')->orderBy('received_at'),
        ])->orderBy('name')->get();

        $readyToDispatch = $metrics->readyToDispatchValue();
        $dispatchedToday = $metrics->assignedTodayValue();
        $ridersWithLoad = $metrics->ridersWithLoadCount();
        $totalRiders = $metrics->coveredAreasCount();
        $avgLoad = $metrics->avgRiderLoad();

        return view('sorting.delivery-assignment', compact(
            'areas', 'readyToDispatch', 'dispatchedToday', 'ridersWithLoad', 'totalRiders', 'avgLoad'
        ));
    }

    /**
     * POST /assignments/bulk
     * Route name: assignments.bulk
     */
    public function bulkAssign(BulkAssignRequest $request, AssignmentService $assignmentService): JsonResponse|RedirectResponse
    {
        try {
            $result = $assignmentService->bulkAssign(
                parcelIds: (array) $request->input('parcel_ids'),
                rider: (int) $request->input('rider_id'),
                user: $request->user()
            );

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json($result);
            }

            return back()
                ->with('status', $result['message'])
                ->with('toast', $result['message']);
        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], 422);
            }

            return back()->withErrors($e->errors());
        }
    }

    /**
     * Dispatch all sorted parcels for a specific area to its rider.
     * Supports preview=1 dry run.
     */
    public function dispatchArea(Request $request, Area $area): JsonResponse|RedirectResponse
    {
        $preview = $request->boolean('preview') || $request->input('preview') == 1;

        if (! $area->rider) {
            $msg = "No rider assigned to {$area->name}.";
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        if (! $area->rider->isActive()) {
            $msg = "Rider {$area->rider->name} is inactive.";
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        $stagedParcels = Parcel::where('area_id', $area->id)
            ->where('status', 'sorted')
            ->get();

        if ($preview) {
            return response()->json([
                'ok' => true,
                'preview' => true,
                'area_id' => $area->id,
                'area_name' => $area->name,
                'rider_id' => $area->rider->id,
                'rider_name' => $area->rider->name,
                'parcels_count' => $stagedParcels->count(),
                'parcel_ids' => $stagedParcels->pluck('id')->all(),
                'message' => "Preview: {$stagedParcels->count()} parcels ready for {$area->rider->name}.",
            ]);
        }

        DB::transaction(function () use ($stagedParcels, $area) {
            foreach ($stagedParcels as $p) {
                $p->update([
                    'status' => 'assigned',
                    'rider_id' => $area->rider->id,
                    'assigned_at' => now(),
                ]);

                ParcelStatusLog::create([
                    'parcel_id' => $p->id,
                    'from' => 'sorted',
                    'to' => 'assigned',
                    'user_id' => auth()->id(),
                    'reason' => "Dispatched area {$area->name} to {$area->rider->name}",
                ]);
            }

            Activity::create([
                'rider_id' => $area->rider->id,
                'icon' => 'green',
                'description' => "<b>{$area->name}</b> — {$stagedParcels->count()} parcels dispatched to <b>{$area->rider->name}</b>",
            ]);
        });

        $msg = "Dispatched {$stagedParcels->count()} parcels for {$area->name} to {$area->rider->name}.";

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $msg,
                'dispatched_count' => $stagedParcels->count(),
            ]);
        }

        return back()->with('status', $msg)->with('toast', $msg);
    }

    /**
     * Dispatch ALL sorted parcels across every area at once.
     * Supports preview=1 dry run.
     */
    public function dispatchAll(Request $request, AssignmentService $assignmentService): JsonResponse|RedirectResponse
    {
        $preview = $request->boolean('preview') || $request->input('preview') == 1;

        $result = $assignmentService->dispatchAll($preview);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json($result);
        }

        if ($preview) {
            return back()->with('preview_data', $result);
        }

        return back()->with('status', $result['message'])->with('toast', $result['message']);
    }

    /**
     * GET /delivery/assignment/dispatch-list
     * Printable dispatch list / preview.
     */
    public function dispatchList(Request $request, AssignmentService $assignmentService): JsonResponse
    {
        $result = $assignmentService->dispatchAll(preview: true);

        return response()->json($result);
    }

    public function generateDispatchList(Request $request, AssignmentService $assignmentService): RedirectResponse|JsonResponse
    {
        $preview = $assignmentService->dispatchAll(preview: true);
        $parcelIds = collect($preview['areas'])->flatMap(fn (array $area) => $area['parcel_ids'])->all();
        $parcels = Parcel::with(['area', 'rider'])
            ->whereIn('id', $parcelIds)
            ->where('status', 'sorted')
            ->orderBy('received_at')
            ->get()
            ->groupBy('area_id');

        $areas = collect($preview['areas'])->map(function (array $area) use ($parcels) {
            $area['parcels'] = $parcels->get($area['area_id'], collect())->map(fn (Parcel $parcel) => [
                'tracking_no' => $parcel->tracking_no,
                'address' => $parcel->address,
                'weight_kg' => $parcel->weight_kg,
            ])->values()->all();
            $area['parcels_count'] = count($area['parcels']);
            unset($area['parcel_ids']);

            return $area;
        })->filter(fn (array $area) => $area['parcels_count'] > 0)->values()->all();

        $manifest = DispatchManifest::create([
            'generated_by' => $request->user()?->id,
            'manifest' => [
                'generated_at' => now()->toIso8601String(),
                'parcels_count' => collect($areas)->sum('parcels_count'),
                'clusters_count' => count($areas),
                'unassigned_clusters' => $preview['unassigned_clusters'],
                'areas' => $areas,
            ],
        ]);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'manifest_id' => $manifest->id,
                'url' => route('delivery.assignment.dispatch-list.show', $manifest),
            ], 201);
        }

        return redirect()->route('delivery.assignment.dispatch-list.show', $manifest);
    }

    public function showDispatchList(DispatchManifest $manifest): View
    {
        return view('sorting.dispatch-list', compact('manifest'));
    }

    public function weighIns(): View
    {
        $parcels = Parcel::with(['area', 'weighIns.user'])
            ->where('status', 'sorted')
            ->orderBy('received_at')
            ->get();

        return view('sorting.weigh-ins', [
            'parcels' => $parcels,
            'weighedCount' => $parcels->whereNotNull('weight_kg')->count(),
        ]);
    }

    public function recordWeighIn(Request $request, Parcel $parcel): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'weight_kg' => 'required|numeric|min:0.01|max:1000',
        ]);

        if ($parcel->status !== 'sorted') {
            $exception = ValidationException::withMessages([
                'parcel' => ['Only parcels in the staging bay can be weighed.'],
            ]);

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => $exception->getMessage(), 'errors' => $exception->errors()], 422);
            }

            return back()->withErrors($exception->errors())->withInput();
        }

        $weighIn = DB::transaction(function () use ($parcel, $request, $data): ParcelWeighIn {
            $parcel->update(['weight_kg' => $data['weight_kg']]);

            return ParcelWeighIn::create([
                'parcel_id' => $parcel->id,
                'user_id' => $request->user()?->id,
                'weight_kg' => $data['weight_kg'],
                'recorded_at' => now(),
            ]);
        });

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'message' => "Weight recorded for {$parcel->tracking_no}.",
                'weight_kg' => $weighIn->weight_kg,
            ]);
        }

        return back()->with('toast', "Weight recorded for {$parcel->tracking_no}.")->with('toast_type', 'success');
    }
}
