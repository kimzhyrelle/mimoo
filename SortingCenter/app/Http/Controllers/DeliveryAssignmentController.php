<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Area;
use App\Models\Parcel;
use Illuminate\Http\Request;

class DeliveryAssignmentController extends Controller
{
    public function index()
    {
        // All areas with their rider and sorted-but-unassigned parcels
        $areas = Area::with([
            'rider',
            'parcels' => fn($q) => $q->where('status', 'sorted')->orderBy('received_at'),
        ])->orderBy('name')->get();

        $readyToDispatch = Parcel::where('status', 'sorted')->count();
        $dispatchedToday = Parcel::where('status', 'assigned')
            ->whereDate('assigned_at', today())->count();
        $ridersWithLoad  = $areas->filter(fn($a) => $a->rider && $a->parcels->count() > 0)->count();
        $totalRiders     = $areas->filter(fn($a) => $a->rider)->count();
        $avgLoad         = $totalRiders
            ? round($areas->filter(fn($a) => $a->rider)->sum(fn($a) => $a->parcels->count()) / $totalRiders, 1)
            : 0;

        return view('sorting.delivery-assignment', compact(
            'areas', 'readyToDispatch', 'dispatchedToday', 'ridersWithLoad', 'totalRiders', 'avgLoad'
        ));
    }

    /** Dispatch all sorted parcels for a specific area to its rider */
    public function dispatchArea(Area $area)
    {
        if (! $area->rider) {
            return back()->with('error', "No rider assigned to {$area->name}.");
        }

        Parcel::where('area_id', $area->id)
            ->where('status', 'sorted')
            ->update([
                'status'      => 'assigned',
                'rider_id'    => $area->rider->id,
                'assigned_at' => now(),
            ]);

        $count = Parcel::where('area_id', $area->id)->where('status', 'assigned')->where('assigned_at', '>=', now()->subSeconds(5))->count();

        Activity::create([
            'rider_id'    => $area->rider->id,
            'icon'        => 'green',
            'description' => "<b>{$area->name}</b> — {$count} " . ($count === 1 ? 'parcel' : 'parcels') . " dispatched to <b>{$area->rider->name}</b>",
        ]);

        return back()->with('status', "Dispatched all parcels for {$area->name} to {$area->rider->name}.");
    }

    /** Dispatch ALL sorted parcels across every area at once */
    public function dispatchAll()
    {
        $areas = Area::with('rider')->get();

        foreach ($areas as $area) {
            if (! $area->rider) continue;

            Parcel::where('area_id', $area->id)
                ->where('status', 'sorted')
                ->update([
                    'status'      => 'assigned',
                    'rider_id'    => $area->rider->id,
                    'assigned_at' => now(),
                ]);
        }

        Activity::create([
            'icon'        => 'green',
            'description' => 'All columns dispatched — every sorted parcel assigned to its rider',
        ]);

        return back()->with('status', 'All columns dispatched successfully.');
    }
}
