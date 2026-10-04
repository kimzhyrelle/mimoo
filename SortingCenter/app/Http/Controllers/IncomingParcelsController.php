<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Area;
use App\Models\Parcel;
use Illuminate\Http\Request;

class IncomingParcelsController extends Controller
{
    public function index(Request $request)
    {
        $q      = $request->query('q');
        $areaId = $request->query('area_id');

        $parcels = Parcel::with(['droppedOffBy', 'area'])
            ->where('status', 'dropped_off')
            ->when($q, fn ($query) => $query
                ->where(fn ($w) => $w
                    ->where('tracking_no', 'like', "%{$q}%")
                    ->orWhere('seller_name', 'like', "%{$q}%")))
            ->when($areaId, fn ($query) => $query->where('area_id', $areaId))
            ->orderBy('received_at')
            ->get();

        $scannedToday = Parcel::whereDate('sorted_at', today())
            ->where('status', '!=', 'dropped_off')
            ->count();

        $areas = Area::whereNotNull('pickup_window')->orderBy('name')->get();

        return view('sorting.incoming-parcels', [
            'parcels'           => $parcels,
            'awaitingScan'      => $parcels->count(),
            'scannedToday'      => $scannedToday,
            'ridersDroppingOff' => Parcel::where('status', 'dropped_off')->distinct('dropped_off_by_rider_id')->count('dropped_off_by_rider_id'),
            'areas'             => $areas,
            'allAreas'          => Area::orderBy('name')->get(),
            'q'                 => $q,
            'area_id'           => $areaId,
        ]);
    }

    public function scan(Parcel $parcel)
    {
        $parcel->update(['status' => 'awaiting_sort', 'sorted_at' => null]);

        Activity::create([
            'parcel_id' => $parcel->id,
            'icon' => 'accent',
            'description' => "<b>{$parcel->tracking_no}</b> scanned, sent to sorting",
        ]);

        return back()->with('status', "{$parcel->tracking_no} scanned.");
    }

    public function scanAll()
    {
        $parcels = Parcel::where('status', 'dropped_off')->get();

        foreach ($parcels as $parcel) {
            $parcel->update(['status' => 'awaiting_sort']);
        }

        if ($parcels->isNotEmpty()) {
            Activity::create([
                'icon' => 'accent',
                'description' => 'Seller drop-off received, <b>'.$parcels->count().' parcels</b> scanned',
            ]);
        }

        return back()->with('status', $parcels->count().' parcels scanned.');
    }
}
