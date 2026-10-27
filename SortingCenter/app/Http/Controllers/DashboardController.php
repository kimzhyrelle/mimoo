<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Area;
use App\Models\Parcel;
use App\Models\Rider;
use App\Services\MetricsService;
use App\Services\ParcelStatusService;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(MetricsService $metrics, ParcelStatusService $statusService)
    {
        $today = Carbon::today();

        $receivedToday = $metrics->parcelsTotalTodayValue();
        $receivedYesterday = $metrics->parcelsReceivedYesterday();

        $awaitingSort = $metrics->parcelsAwaitingSortValue();
        $assignedToRiders = $metrics->parcelsAssignedToRidersValue();
        $deliveryFailed = $metrics->overdueCountValue();

        $deliveredToday = $metrics->parcelsDeliveredTodayValue();
        $onTimePct = $metrics->onTimePercentage();

        $ridersActive = $metrics->ridersOnDutyValue();
        $priorityCount = $metrics->priorityCount();
        $slaPct = $metrics->slaPercentage();

        $hourly = $metrics->hourlyProcessed($today);
        $hourly_processed = $hourly;

        $statusBreakdown = $metrics->statusBreakdown();
        $status_breakdown = $metrics->statusBreakdownList();

        $recentShipments = Parcel::with('area')->orderByDesc('received_at')->limit(50)->get();
        $recentActivity = Activity::latest()->limit(4)->get();

        // Feed formatted for Kiro: [{icon, text, url, at}]
        $activity = $statusService->getActivityFeed(10);

        // Kept for the Rider status widgets / legacy partials
        $areas = Area::withCount([
            'parcels as parcel_count' => fn ($q) => $q->whereDate('received_at', $today),
        ])->orderByDesc('parcel_count')->get();
        $riders = Rider::with('area')->orderBy('name')->get();

        return view('sorting.dashboard', compact(
            'receivedToday', 'receivedYesterday', 'awaitingSort',
            'assignedToRiders', 'deliveryFailed',
            'deliveredToday', 'onTimePct', 'ridersActive', 'priorityCount', 'slaPct',
            'hourly', 'hourly_processed', 'statusBreakdown', 'status_breakdown',
            'recentShipments', 'recentActivity', 'activity',
            'areas', 'riders'
        ));
    }
}
