<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ParcelResource;
use App\Models\Area;
use App\Models\Activity;
use App\Models\Parcel;
use App\Models\Rider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParcelController extends Controller
{
    /** GET /api/parcels */
    public function index(Request $request): JsonResponse
    {
        $query = Parcel::with(['area', 'rider', 'droppedOffBy'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->area_id, fn ($q, $a) => $q->where('area_id', $a))
            ->when($request->q, fn ($q, $search) =>
                $q->where(fn ($q) =>
                    $q->where('tracking_no', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%")
                      ->orWhere('seller_name', 'like', "%{$search}%")
                )
            )
            ->orderBy('received_at', 'asc');

        $parcels = $query->paginate($request->per_page ?? 20);

        return response()->json([
            'data' => ParcelResource::collection($parcels->items()),
            'meta' => [
                'total'        => $parcels->total(),
                'per_page'     => $parcels->perPage(),
                'current_page' => $parcels->currentPage(),
                'last_page'    => $parcels->lastPage(),
            ],
        ]);
    }

    /** GET /api/parcels/{parcel} */
    public function show(Parcel $parcel): JsonResponse
    {
        $parcel->load(['area', 'rider', 'droppedOffBy']);

        return response()->json(['data' => new ParcelResource($parcel)]);
    }

    /** POST /api/parcels/{parcel}/scan — mark as arrived at hub */
    public function scan(Parcel $parcel): JsonResponse
    {
        if ($parcel->status !== 'dropped_off') {
            return response()->json(['message' => 'Parcel is not in dropped-off state.'], 422);
        }

        $parcel->update([
            'status'      => 'awaiting_sort',
            'received_at' => now(),
        ]);

        Activity::create([
            'parcel_id'   => $parcel->id,
            'icon'        => 'accent',
            'description' => "<b>{$parcel->tracking_no}</b> scanned, sent to sorting",
        ]);

        return response()->json([
            'data'    => new ParcelResource($parcel->fresh(['area', 'rider'])),
            'message' => 'Parcel scanned successfully.',
        ]);
    }

    /** POST /api/parcels/{parcel}/determine-area */
    public function determineArea(Request $request, Parcel $parcel): JsonResponse
    {
        $data = $request->validate([
            'area_id' => 'required|exists:areas,id',
            'carrier' => 'nullable|string|in:jnt,lbc,lalamove',
        ]);

        $area = Area::findOrFail($data['area_id']);

        $parcel->update([
            'area_id' => $area->id,
            'status'  => 'sorted',
            'carrier' => $data['carrier'] ?? $parcel->carrier,
            'sorted_at' => now(),
        ]);

        return response()->json([
            'data'    => new ParcelResource($parcel->fresh(['area', 'rider'])),
            'message' => "Parcel assigned to Brgy. {$area->name}.",
        ]);
    }

    /** POST /api/parcels/{parcel}/assign-rider */
    public function assignRider(Request $request, Parcel $parcel): JsonResponse
    {
        $data = $request->validate([
            'rider_id' => 'required|exists:riders,id',
        ]);

        $rider = Rider::findOrFail($data['rider_id']);

        $parcel->update([
            'rider_id'    => $rider->id,
            'status'      => 'sorted',
            'assigned_at' => now(),
        ]);

        return response()->json([
            'data'    => new ParcelResource($parcel->fresh(['area', 'rider'])),
            'message' => "Rider {$rider->name} assigned to parcel.",
        ]);
    }

    /** POST /api/parcels/{parcel}/dispatch — sorted → assigned */
    public function dispatch(Parcel $parcel): JsonResponse
    {
        if ($parcel->status !== 'sorted') {
            return response()->json(['message' => 'Parcel must be sorted before dispatch.'], 422);
        }

        $parcel->update([
            'status'      => 'assigned',
            'assigned_at' => now(),
        ]);

        Activity::create([
            'parcel_id'   => $parcel->id,
            'rider_id'    => $parcel->rider_id,
            'icon'        => 'green',
            'description' => "<b>{$parcel->tracking_no}</b> dispatched to <b>{$parcel->rider?->name}</b>",
        ]);

        return response()->json([
            'data'    => new ParcelResource($parcel->fresh(['area', 'rider'])),
            'message' => 'Parcel dispatched.',
        ]);
    }

    /** POST /api/parcels/{parcel}/advance — move delivery status forward */
    public function advance(Parcel $parcel): JsonResponse
    {
        $next = match ($parcel->status) {
            'assigned'        => ['status' => 'out_for_delivery'],
            'out_for_delivery'=> ['status' => 'delivered', 'delivered_at' => now()],
            default           => null,
        };

        if (! $next) {
            return response()->json(['message' => 'Cannot advance from current status.'], 422);
        }

        $parcel->update($next);

        return response()->json([
            'data'    => new ParcelResource($parcel->fresh(['area', 'rider'])),
            'message' => "Status updated to {$parcel->status}.",
        ]);
    }

    /** POST /api/parcels/{parcel}/scan-undelivered */
    public function scanUndelivered(Parcel $parcel): JsonResponse
    {
        $parcel->update([
            'status'              => 'failed',
            'scanned_undelivered' => true,
        ]);

        return response()->json([
            'data'    => new ParcelResource($parcel->fresh(['area', 'rider'])),
            'message' => 'Parcel marked as confirmed undelivered.',
        ]);
    }
}
