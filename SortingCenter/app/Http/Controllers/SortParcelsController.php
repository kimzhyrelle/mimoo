<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Area;
use App\Models\Parcel;
use Illuminate\Http\Request;

class SortParcelsController extends Controller
{
    public function index(Request $request)
    {
        $q      = $request->query('q');
        $areaId = $request->query('area_id');

        $parcels = Parcel::with(['area', 'rider'])
            ->whereIn('status', ['awaiting_sort', 'sorted', 'assigned'])
            ->when($q, fn ($query) => $query
                ->where(fn ($w) => $w
                    ->where('tracking_no', 'like', "%{$q}%")
                    ->orWhere('address', 'like', "%{$q}%")))
            ->when($areaId, fn ($query) => $query->where('area_id', $areaId))
            ->orderBy('received_at')
            ->get();

        $areas = Area::with('rider')->orderBy('name')->get();

        return view('sorting.sort-parcels', [
            'parcels'       => $parcels,
            'areas'         => $areas,
            'allAreas'      => $areas,
            'awaitingSort'  => Parcel::where('status', 'awaiting_sort')->count(),
            'areaDetermined'=> Parcel::whereIn('status', ['sorted', 'assigned'])->count(),
            'assignedToday' => Parcel::where('status', 'assigned')->whereDate('assigned_at', today())->count(),
            'ridersOnShift' => \App\Models\Rider::where('status', '!=', 'offline')->count(),
            'q'             => $q,
            'area_id'       => $areaId,
        ]);
    }

    // Match the parcel's address text against the hub's known barangays.
    public function determineArea(Parcel $parcel)
    {
        $address = strtolower($parcel->address);
        $area = Area::get()->first(fn ($a) => str_contains($address, strtolower(str_replace('Brgy. ', '', $a->name))));

        $parcel->update([
            'area_id' => $area?->id,
            'status' => 'sorted',
            'sorted_at' => now(),
        ]);

        return back()->with('status', $area
            ? "{$parcel->tracking_no} sorted to {$area->name}."
            : "{$parcel->tracking_no} could not be matched to a barangay — set it manually.");
    }

    public function assignRider(Parcel $parcel)
    {
        if (! $parcel->area_id) {
            return back()->withErrors('Determine the barangay before assigning a rider.');
        }

        $rider = $parcel->area->rider;

        if (! $rider) {
            return back()->withErrors("No rider is assigned to {$parcel->area->name} yet — assign one in Rider Management first.");
        }

        $parcel->update([
            'rider_id' => $rider->id,
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);

        Activity::create([
            'parcel_id' => $parcel->id,
            'rider_id' => $rider->id,
            'icon' => 'green',
            'description' => "<b>{$parcel->tracking_no}</b> assigned to {$rider->name}",
        ]);

        return back()->with('status', "{$parcel->tracking_no} assigned to {$rider->name}.");
    }

    public function setCarrier(Request $request, Parcel $parcel)
    {
        $request->validate(['carrier' => 'required|in:jnt,lbc,lalamove']);
        $parcel->update(['carrier' => $request->carrier]);

        return back();
    }
}
