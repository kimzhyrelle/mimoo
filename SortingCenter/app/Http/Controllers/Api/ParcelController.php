<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ParcelResource;
use App\Models\Area;
use App\Models\Parcel;
use App\Models\Rider;
use App\Services\ParcelStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ParcelController extends Controller
{
    public function __construct(
        protected ParcelStatusService $statusService
    ) {}

    /** GET /api/parcels */
    public function index(Request $request): JsonResponse
    {
        $query = Parcel::with(['area', 'rider', 'droppedOffBy'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->area_id, fn ($q, $a) => $q->where('area_id', $a))
            ->when($request->q, fn ($q, $search) => $q->where(fn ($q) => $q->where('tracking_no', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%")
                ->orWhere('seller_name', 'like', "%{$search}%")
            )
            )
            ->orderBy('received_at', 'asc');

        $parcels = $query->paginate($request->per_page ?? 20);

        return response()->json([
            'ok' => true,
            'data' => ParcelResource::collection($parcels->items()),
            'meta' => [
                'total' => $parcels->total(),
                'per_page' => $parcels->perPage(),
                'current_page' => $parcels->currentPage(),
                'last_page' => $parcels->lastPage(),
            ],
        ]);
    }

    /** GET /api/parcels/{parcel} */
    public function show(Parcel $parcel): JsonResponse
    {
        $parcel->load(['area', 'rider', 'droppedOffBy']);

        return response()->json([
            'ok' => true,
            'data' => new ParcelResource($parcel),
        ]);
    }

    /** POST /api/parcels/{parcel}/scan — mark as arrived at hub */
    public function scan(Parcel $parcel): JsonResponse
    {
        if ($parcel->isDelivered()) {
            return response()->json(['ok' => false, 'message' => 'Delivered parcels cannot be modified.'], 422);
        }

        if ($parcel->status !== 'dropped_off') {
            return response()->json(['ok' => false, 'message' => 'Parcel is not in dropped-off state.'], 422);
        }

        try {
            $result = $this->statusService->transition($parcel, 'awaiting_sort', 'Scanned at inbound dock');

            return response()->json([
                'ok' => true,
                'message' => 'Parcel scanned successfully.',
                'undo_token' => $result['undo_token'],
                'data' => new ParcelResource($result['parcel']),
            ]);
        } catch (ValidationException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        }
    }

    /** POST /api/parcels/{parcel}/determine-area */
    public function determineArea(Request $request, Parcel $parcel): JsonResponse
    {
        if ($parcel->isDelivered()) {
            return response()->json(['ok' => false, 'message' => 'Delivered parcels cannot be modified.'], 422);
        }

        $data = $request->validate([
            'area_id' => 'required|exists:areas,id',
            'carrier' => 'nullable|string|in:jnt,lbc,lalamove',
        ]);

        $area = Area::findOrFail($data['area_id']);

        $parcel->update([
            'area_id' => $area->id,
            'carrier' => $data['carrier'] ?? $parcel->carrier,
        ]);

        try {
            $result = $this->statusService->transition($parcel, 'sorted', "Sorted to {$area->name}");

            return response()->json([
                'ok' => true,
                'message' => "Parcel assigned to Brgy. {$area->name}.",
                'undo_token' => $result['undo_token'],
                'data' => new ParcelResource($result['parcel']),
            ]);
        } catch (ValidationException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        }
    }

    /** POST /api/parcels/{parcel}/assign-rider */
    public function assignRider(Request $request, Parcel $parcel): JsonResponse
    {
        if ($parcel->isDelivered()) {
            return response()->json(['ok' => false, 'message' => 'Delivered parcels cannot be reassigned.'], 422);
        }

        $data = $request->validate([
            'rider_id' => 'required|exists:riders,id',
        ]);

        $rider = Rider::findOrFail($data['rider_id']);

        if (! $rider->isActive()) {
            return response()->json(['ok' => false, 'message' => 'Assigned rider is not active.'], 422);
        }

        $parcel->update([
            'rider_id' => $rider->id,
        ]);

        try {
            $result = $this->statusService->transition($parcel, 'assigned', "Assigned to {$rider->name}");

            return response()->json([
                'ok' => true,
                'message' => "Rider {$rider->name} assigned to parcel.",
                'undo_token' => $result['undo_token'],
                'data' => new ParcelResource($result['parcel']),
            ]);
        } catch (ValidationException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        }
    }

    /** POST /api/parcels/{parcel}/dispatch — sorted → assigned */
    public function dispatch(Parcel $parcel): JsonResponse
    {
        if ($parcel->isDelivered()) {
            return response()->json(['ok' => false, 'message' => 'Delivered parcels cannot be dispatched.'], 422);
        }

        if ($parcel->status !== 'sorted') {
            return response()->json(['ok' => false, 'message' => 'Parcel must be sorted before dispatch.'], 422);
        }

        try {
            $result = $this->statusService->transition($parcel, 'assigned', 'Dispatched to rider');

            return response()->json([
                'ok' => true,
                'message' => 'Parcel dispatched.',
                'undo_token' => $result['undo_token'],
                'data' => new ParcelResource($result['parcel']),
            ]);
        } catch (ValidationException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        }
    }

    /** POST /api/parcels/{parcel}/advance — move delivery status forward */
    public function advance(Parcel $parcel): JsonResponse
    {
        if ($parcel->isDelivered()) {
            return response()->json(['ok' => false, 'message' => 'Delivered parcels cannot change status.'], 422);
        }

        $nextStatus = match ($parcel->status) {
            'assigned' => 'out_for_delivery',
            'out_for_delivery' => 'delivered',
            default => null,
        };

        if (! $nextStatus) {
            return response()->json(['ok' => false, 'message' => 'Cannot advance from current status.'], 422);
        }

        try {
            $result = $this->statusService->transition($parcel, $nextStatus);

            return response()->json([
                'ok' => true,
                'message' => "Status updated to {$parcel->fresh()->status}.",
                'undo_token' => $result['undo_token'],
                'data' => new ParcelResource($result['parcel']),
            ]);
        } catch (ValidationException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        }
    }

    /** POST /api/parcels/{parcel}/scan-undelivered */
    public function scanUndelivered(Parcel $parcel): JsonResponse
    {
        if ($parcel->isDelivered()) {
            return response()->json(['ok' => false, 'message' => 'Delivered parcels cannot be marked failed.'], 422);
        }

        try {
            $result = $this->statusService->transition($parcel, 'failed', 'Confirmed undelivered');

            return response()->json([
                'ok' => true,
                'message' => 'Parcel marked as confirmed undelivered.',
                'undo_token' => $result['undo_token'],
                'data' => new ParcelResource($result['parcel']),
            ]);
        } catch (ValidationException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        }
    }
}
