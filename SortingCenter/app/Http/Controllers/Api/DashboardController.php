<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Parcel;
use App\Services\MetricsService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /** GET /api/dashboard */
    public function index(MetricsService $metrics): JsonResponse
    {
        $receivedToday = $metrics->parcelsTotalTodayValue();
        $receivedYesterday = $metrics->parcelsReceivedYesterday();
        $awaitingSort = $metrics->parcelsAwaitingSortValue();
        $sorted = $metrics->readyToDispatchValue();
        $outForDelivery = Parcel::where('status', 'out_for_delivery')->count();
        $assignedToRiders = Parcel::where('status', 'assigned')->count();
        $deliveredToday = $metrics->parcelsDeliveredTodayValue();
        $failedToday = $metrics->overdueCountValue();
        $ridersOnShift = $metrics->ridersOnDutyValue();
        $ridersAvailable = $metrics->ridersIdleValue();
        $ridersBusy = $metrics->ridersInRouteValue();

        $areaStats = Area::with(['rider'])
            ->withCount(['parcels as sorted_count' => fn ($q) => $q->where('status', 'sorted')])
            ->orderBy('name')
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'code' => $a->code,
                'color' => $a->chart_color,
                'sorted_count' => $a->sorted_count,
                'rider' => $a->rider ? [
                    'name' => $a->rider->name,
                    'status' => $a->rider->status,
                ] : null,
            ]);

        return response()->json([
            'data' => [
                'parcels' => [
                    'received_today' => $receivedToday,
                    'received_yesterday' => $receivedYesterday,
                    'awaiting_sort' => $awaitingSort,
                    'sorted' => $sorted,
                    'assigned_to_riders' => $assignedToRiders,
                    'out_for_delivery' => $outForDelivery,
                    'delivered_today' => $deliveredToday,
                    'failed_today' => $failedToday,
                ],
                'riders' => [
                    'on_shift' => $ridersOnShift,
                    'available' => $ridersAvailable,
                    'busy' => $ridersBusy,
                ],
                'areas' => $areaStats,
            ],
        ]);
    }
}
