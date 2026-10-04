<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Parcel;
use Illuminate\Http\Request;

class DeliveryMonitoringController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'all');
        $q      = $request->query('q');
        $areaId = $request->query('area_id');

        $parcels = Parcel::with(['area', 'rider'])
            ->whereIn('status', ['assigned', 'out_for_delivery', 'delivered', 'failed'])
            ->when($filter !== 'all', fn ($query) => $query->where('status', match ($filter) {
                'assigned'  => 'assigned',
                'out'       => 'out_for_delivery',
                'delivered' => 'delivered',
                'failed'    => 'failed',
                default     => $filter,
            }))
            ->when($q, fn ($query) => $query
                ->where(fn ($w) => $w
                    ->where('tracking_no', 'like', "%{$q}%")
                    ->orWhereHas('rider', fn ($r) => $r->where('name', 'like', "%{$q}%"))))
            ->when($areaId, fn ($query) => $query->where('area_id', $areaId))
            ->orderByDesc('assigned_at')
            ->get();

        $rail = [
            'sorted'          => Parcel::where('status', 'sorted')->count(),
            'assigned'        => Parcel::where('status', 'assigned')->count(),
            'out'             => Parcel::where('status', 'out_for_delivery')->count(),
            'delivered'       => Parcel::where('status', 'delivered')->count(),
            'pending_confirm' => Parcel::where('status', 'delivered')->whereNull('buyer_confirmed_at')->count(),
            'failed'          => Parcel::where('status', 'failed')->count(),
        ];

        return view('sorting.delivery-monitoring', [
            'parcels' => $parcels,
            'rail'    => $rail,
            'filter'  => $filter,
            'q'       => $q,
            'area_id' => $areaId,
            'allAreas'=> \App\Models\Area::orderBy('name')->get(),
        ]);
    }

    // assigned -> out_for_delivery -> delivered
    public function advance(Parcel $parcel)
    {
        if ($parcel->status === 'assigned') {
            $parcel->update(['status' => 'out_for_delivery', 'eta_note' => '18 min']);

            Activity::create([
                'parcel_id'   => $parcel->id,
                'rider_id'    => $parcel->rider_id,
                'icon'        => 'accent',
                'description' => "<b>{$parcel->tracking_no}</b> is now out for delivery"
                    . ($parcel->rider ? " with {$parcel->rider->name}" : ''),
            ]);

        } elseif ($parcel->status === 'out_for_delivery') {
            $parcel->update(['status' => 'delivered', 'delivered_at' => now(), 'eta_note' => null]);

            Activity::create([
                'parcel_id'   => $parcel->id,
                'rider_id'    => $parcel->rider_id,
                'icon'        => 'green',
                'description' => "<b>{$parcel->tracking_no}</b> delivered successfully"
                    . ($parcel->rider ? " by {$parcel->rider->name}" : ''),
            ]);
        }

        return back();
    }

    // The rider scans the parcel at end of day to confirm it wasn't delivered.
    public function scanUndelivered(Parcel $parcel)
    {
        $parcel->update([
            'scanned_undelivered' => true,
            'eta_note'            => 'Reschedule / return to hub',
        ]);

        Activity::create([
            'parcel_id'   => $parcel->id,
            'rider_id'    => $parcel->rider_id,
            'icon'        => 'red',
            'description' => "<b>{$parcel->tracking_no}</b> confirmed undelivered — needs reschedule"
                . ($parcel->rider ? " (scanned by {$parcel->rider->name})" : ''),
        ]);

        return back();
    }
}
