<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Rider;
use App\Models\RiderApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RiderManagementController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');

        $pending = RiderApplication::where('status', 'pending')->orderBy('applied_on')->get();

        $riders = Rider::with('area')
            ->when($q, fn ($query) => $query
                ->where(fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('initials', 'like', "%{$q}%")))
            ->orderBy('name')
            ->get();

        $areas = Area::with('rider')->orderBy('name')->get();
        $covered = $areas->filter(fn ($a) => $a->rider)->count();

        return view('sorting.rider-management', [
            'pending' => $pending,
            'riders' => $riders,
            'areas' => $areas,
            'covered' => $covered,
            'unassigned' => $areas->count() - $covered,
            'onShift' => $riders->filter->isActive()->count(),
            'q' => $q,
        ]);
    }

    public function approve(RiderApplication $application)
    {
        $rider = DB::transaction(function () use ($application) {
            $initials = 'R'.str_pad((string) (Rider::count() + 1), 2, '0', STR_PAD_LEFT);

            $rider = Rider::create([
                'name' => $application->name,
                'initials' => $initials,
                'vehicle_type' => $application->vehicle_type,
                'plate_no' => $application->plate_no,
                'status' => 'available',
            ]);

            $application->update(['status' => 'approved', 'approved_rider_id' => $rider->id]);

            return $rider;
        });

        return back()->with('status', "{$application->name} approved as {$rider->initials}. Assign a barangay below.");
    }

    public function disapprove(RiderApplication $application)
    {
        $application->update(['status' => 'rejected']);

        return back();
    }

    // One rider per barangay — reassigning frees it from whoever had it before.
    public function setBarangay(Request $request, Rider $rider)
    {
        $data = $request->validate(['area_id' => 'nullable|exists:areas,id']);

        DB::transaction(function () use ($data, $rider) {
            if (! empty($data['area_id'])) {
                Rider::where('area_id', $data['area_id'])->where('id', '!=', $rider->id)->update(['area_id' => null]);
            }
            $rider->update(['area_id' => $data['area_id'] ?? null]);
        });

        return back();
    }

    public function toggleActive(Rider $rider)
    {
        $rider->update(['status' => $rider->isActive() ? 'offline' : 'available']);

        return back();
    }
}
