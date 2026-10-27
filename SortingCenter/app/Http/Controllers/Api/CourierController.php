<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ParcelResource;
use App\Http\Resources\RiderResource;
use App\Models\Parcel;
use App\Models\Rider;
use App\Services\MetricsService;
use App\Services\ParcelStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CourierController extends Controller
{
    public function __construct(
        protected ParcelStatusService $statusService,
        protected MetricsService $metrics
    ) {}

    /** GET /api/courier/{rider} */
    public function show(Request $request, Rider $rider): JsonResponse
    {
        $this->authorizeRider($request, $rider);

        $rider->load('area')->loadCount($this->countRelations());

        $parcels = $rider->parcels()
            ->with(['area', 'rider'])
            ->whereIn('status', ['assigned', 'out_for_delivery'])
            ->orderByRaw("CASE status WHEN 'out_for_delivery' THEN 0 ELSE 1 END")
            ->orderBy('assigned_at')
            ->get();

        return response()->json([
            'ok' => true,
            'data' => [
                'rider' => new RiderResource($rider),
                'counts' => $this->courierCounts($rider),
                'parcels' => ParcelResource::collection($parcels),
            ],
            'meta' => [
                'fleet' => $this->fleetMetrics(),
            ],
        ]);
    }

    /** POST /api/courier/{rider}/claim */
    public function claim(Request $request, Rider $rider): JsonResponse
    {
        $this->authorizeRider($request, $rider);

        if (! $rider->isActive()) {
            return response()->json(['ok' => false, 'message' => 'This rider is inactive.'], 422);
        }

        $data = $request->validate([
            'parcel_id' => [
                'required',
                'integer',
                Rule::exists('parcels', 'id')->whereNull('deleted_at'),
            ],
        ]);

        try {
            [$parcel, $result] = DB::transaction(function () use ($data, $rider, $request) {
                $parcel = Parcel::query()
                    ->with(['area', 'rider'])
                    ->lockForUpdate()
                    ->findOrFail($data['parcel_id']);

                if (! in_array($parcel->status, ['sorted', 'assigned'], true)) {
                    throw ValidationException::withMessages([
                        'parcel_id' => ['Only sorted parcels can be claimed.'],
                    ]);
                }

                if ($parcel->rider_id !== null && (int) $parcel->rider_id !== (int) $rider->id) {
                    throw ValidationException::withMessages([
                        'parcel_id' => ['This parcel is already assigned to another rider.'],
                    ]);
                }

                if ($rider->area_id && (int) $parcel->area_id !== (int) $rider->area_id) {
                    throw ValidationException::withMessages([
                        'parcel_id' => ['This parcel belongs to another barangay route.'],
                    ]);
                }

                $parcel->update(['rider_id' => $rider->id]);

                $result = $this->statusService->transition(
                    $parcel,
                    'assigned',
                    "Claimed by {$rider->initials} via courier app",
                    $request->user()
                );

                return [$parcel, $result];
            });
        } catch (ValidationException $exception) {
            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
                'errors' => $exception->errors(),
            ], 422);
        }

        $rider->refresh()->load('area')->loadCount($this->countRelations());

        return response()->json([
            'ok' => true,
            'message' => $result['message'] ?? "Parcel {$parcel->tracking_no} claimed.",
            'undo_token' => $result['undo_token'] ?? null,
            'data' => [
                'parcel' => new ParcelResource($parcel->fresh(['area', 'rider'])),
                'rider' => new RiderResource($rider),
                'counts' => $this->courierCounts($rider),
            ],
        ]);
    }

    /** POST /api/courier/{rider}/advance */
    public function advance(Request $request, Rider $rider): JsonResponse
    {
        $this->authorizeRider($request, $rider);

        if (! $rider->isActive()) {
            return response()->json(['ok' => false, 'message' => 'This rider is inactive.'], 422);
        }

        $data = $request->validate([
            'parcel_id' => [
                'required',
                'integer',
                Rule::exists('parcels', 'id')->whereNull('deleted_at'),
            ],
        ]);

        $parcel = Parcel::query()
            ->with(['area', 'rider'])
            ->where('rider_id', $rider->id)
            ->find($data['parcel_id']);

        if (! $parcel) {
            return response()->json(['ok' => false, 'message' => 'This parcel is not assigned to this rider.'], 404);
        }

        $nextStatus = match ($parcel->status) {
            'assigned' => 'out_for_delivery',
            'out_for_delivery' => 'delivered',
            default => null,
        };

        if ($nextStatus === null) {
            return response()->json([
                'ok' => false,
                'message' => "Cannot advance a parcel from status {$parcel->status}.",
            ], 422);
        }

        try {
            $result = $this->statusService->transition($parcel, $nextStatus, null, $request->user());
        } catch (ValidationException $exception) {
            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
                'errors' => $exception->errors(),
            ], 422);
        }

        $rider->refresh()->load('area')->loadCount($this->countRelations());

        return response()->json([
            'ok' => true,
            'message' => $result['message'] ?? "Parcel moved to {$nextStatus}.",
            'undo_token' => $result['undo_token'] ?? null,
            'data' => [
                'parcel' => new ParcelResource($parcel->fresh(['area', 'rider'])),
                'rider' => new RiderResource($rider),
                'counts' => $this->courierCounts($rider),
            ],
        ]);
    }

    private function authorizeRider(Request $request, Rider $rider): void
    {
        $user = $request->user();

        if ($user?->isRider() && (int) $rider->user_id !== (int) $user->id) {
            abort(403, 'You may only access your own courier dashboard.');
        }
    }

    /**
     * @return array<string, int>
     */
    private function courierCounts(Rider $rider): array
    {
        return [
            'assigned' => $rider->parcels()->where('status', 'assigned')->count(),
            'out_for_delivery' => $rider->parcels()->where('status', 'out_for_delivery')->count(),
            'open' => $rider->parcels()->whereIn('status', ['assigned', 'out_for_delivery'])->count(),
            'delivered_today' => $rider->parcels()
                ->where('status', 'delivered')
                ->whereDate('delivered_at', today())
                ->count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function fleetMetrics(): array
    {
        return [
            'riders_on_duty' => $this->metrics->ridersOnDutyValue(),
            'riders_active' => $this->metrics->ridersActiveValue(),
            'riders_idle' => $this->metrics->ridersIdleValue(),
            'riders_in_route' => $this->metrics->ridersInRouteValue(),
        ];
    }

    /**
     * @return array<string, callable>
     */
    private function countRelations(): array
    {
        return [
            'parcels as open_parcels_count' => fn ($query) => $query->whereIn('status', ['assigned', 'out_for_delivery']),
            'parcels as out_for_delivery_count' => fn ($query) => $query->where('status', 'out_for_delivery'),
            'parcels as delivered_today_count' => fn ($query) => $query->where('status', 'delivered')->whereDate('delivered_at', today()),
        ];
    }
}
