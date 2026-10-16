<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AreaResource;
use App\Models\Activity;
use App\Models\Area;
use App\Models\Parcel;
use Illuminate\Http\JsonResponse;

class DispatchController extends Controller
{
    /** GET /api/dispatch — board: areas with their sorted parcels */
    public function board(): JsonResponse
    {
        $areas = Area::with([
            'rider',
            'parcels' => fn ($q) => $q->where('status', 'sorted')->orderBy('received_at'),
        ])->orderBy('name')->get();

        $readyToDispatch = Parcel::where('status', 'sorted')->count();
        $dispatchedToday = Parcel::where('status', 'assigned')
            ->whereDate('assigned_at', today())->count();

        return response()->json([
            'data' => [
                'areas'            => $areas->map(fn ($area) => [
                    'id'          => $area->id,
                    'name'        => $area->name,
                    'code'        => $area->code,
                    'color'       => $area->chart_color,
                    'rider'       => $area->rider ? [
                        'id'       => $area->rider->id,
                        'name'     => $area->rider->name,
                        'initials' => $area->rider->initials,
                        'status'   => $area->rider->status,
                    ] : null,
                    'parcel_count' => $area->parcels->count(),
                    'parcels'      => $area->parcels->map(fn ($p) => [
                        'id'          => $p->id,
                        'tracking_no' => $p->tracking_no,
                        'address'     => $p->address,
                        'carrier'     => $p->carrier,
                    ]),
                ]),
                'stats' => [
                    'ready_to_dispatch' => $readyToDispatch,
                    'dispatched_today'  => $dispatchedToday,
                ],
            ],
        ]);
    }

    /** POST /api/dispatch/area/{area} — dispatch all sorted parcels for one area */
    public function dispatchArea(Area $area): JsonResponse
    {
        if (! $area->rider) {
            return response()->json(['message' => "No rider assigned to {$area->name}."], 422);
        }

        $count = Parcel::where('area_id', $area->id)
            ->where('status', 'sorted')
            ->update([
                'status'      => 'assigned',
                'rider_id'    => $area->rider->id,
                'assigned_at' => now(),
            ]);

        Activity::create([
            'rider_id'    => $area->rider->id,
            'icon'        => 'green',
            'description' => "<b>{$area->name}</b> — {$count} " . ($count === 1 ? 'parcel' : 'parcels') . " dispatched to <b>{$area->rider->name}</b>",
        ]);

        return response()->json([
            'message' => "Dispatched {$count} parcel(s) to {$area->rider->name}.",
            'data'    => ['dispatched_count' => $count],
        ]);
    }

    /** POST /api/dispatch/all — dispatch every sorted parcel across all areas */
    public function dispatchAll(): JsonResponse
    {
        $areas   = Area::with('rider')->get();
        $total   = 0;

        foreach ($areas as $area) {
            if (! $area->rider) continue;

            $count = Parcel::where('area_id', $area->id)
                ->where('status', 'sorted')
                ->update([
                    'status'      => 'assigned',
                    'rider_id'    => $area->rider->id,
                    'assigned_at' => now(),
                ]);

            $total += $count;
        }

        Activity::create([
            'icon'        => 'green',
            'description' => "All columns dispatched — {$total} " . ($total === 1 ? 'parcel' : 'parcels') . " assigned to riders",
        ]);

        return response()->json([
            'message' => "Dispatched {$total} parcel(s) across all areas.",
            'data'    => ['dispatched_count' => $total],
        ]);
    }
}
