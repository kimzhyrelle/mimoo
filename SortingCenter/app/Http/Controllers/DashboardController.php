<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Area;
use App\Models\Parcel;
use App\Models\Rider;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $receivedToday = Parcel::whereDate('received_at', $today)->count();
        $receivedYesterday = Parcel::whereDate('received_at', $today->copy()->subDay())->count();

        $awaitingSort = Parcel::where('status', 'awaiting_sort')->count();
        $assignedToRiders = Parcel::whereIn('status', ['assigned', 'out_for_delivery'])->count();
        $deliveryFailed = Parcel::where('status', 'failed')->count();

        // Parcel volume this week: received vs. assigned, grouped Mon..Sun
        $weekStart = $today->copy()->startOfWeek();
        $labels = [];
        $received = [];
        $assigned = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $weekStart->copy()->addDays($i);
            $labels[] = $day->format('D');
            $received[] = Parcel::whereDate('received_at', $day)->count();
            $assigned[] = Parcel::whereDate('assigned_at', $day)->count();
        }

        $areas = Area::withCount([
            'parcels as parcel_count' => fn ($q) => $q->whereDate('received_at', $today),
        ])->orderByDesc('parcel_count')->get();

        $riders = Rider::with('area')->orderBy('name')->get();

        $recentActivity = Activity::latest()->limit(4)->get();

        $needsAttention = Parcel::with('area')
            ->whereIn('status', ['awaiting_sort', 'sorted'])
            ->orderBy('received_at')
            ->limit(4)
            ->get();

        return view('sorting.dashboard', compact(
            'receivedToday', 'receivedYesterday', 'awaitingSort',
            'assignedToRiders', 'deliveryFailed',
            'labels', 'received', 'assigned',
            'areas', 'riders', 'recentActivity', 'needsAttention'
        ));
    }
}
