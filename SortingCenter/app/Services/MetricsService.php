<?php

namespace App\Services;

use App\Models\Area;
use App\Models\Parcel;
use App\Models\Rider;
use App\Models\RiderApplication;
use Illuminate\Support\Carbon;

class MetricsService
{
    /**
     * Format a human-readable date range label (e.g. "Today", "Oct 1–24", "Oct 24, 2026").
     */
    public function formatRangeLabel(?Carbon $from = null, ?Carbon $to = null): string
    {
        $today = Carbon::today();
        if ($from === null && $to === null) {
            return 'Today';
        }

        $from = $from ? $from->copy()->startOfDay() : $today->copy()->startOfMonth();
        $to = $to ? $to->copy()->endOfDay() : $today->copy()->endOfDay();

        if ($from->isSameDay($to)) {
            return $from->isToday() ? 'Today' : $from->format('M j');
        }

        if ($from->isSameMonth($to) && $from->isSameYear($to)) {
            return $from->format('M j').'–'.$to->format('j');
        }

        return $from->format('M j').'–'.$to->format('M j');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 9 CANONICAL METRICS (each returning ['name' => string, 'value' => int, 'range_label' => string])
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * 1. Parcels currently in queue awaiting sort or dispatch.
     * Staged in hub: awaiting_sort or sorted.
     */
    public function parcelsInQueue(?Carbon $from = null, ?Carbon $to = null): array
    {
        return [
            'name' => 'parcels_in_queue',
            'value' => $this->parcelsInQueueValue(),
            'range_label' => $this->formatRangeLabel($from, $to),
        ];
    }

    public function parcelsInQueueValue(): int
    {
        return Parcel::whereIn('status', ['awaiting_sort', 'sorted'])->count();
    }

    /**
     * 2. Clusters (barangays) that have parcels in sorted status pending assignment/dispatch.
     */
    public function clustersPending(?Carbon $from = null, ?Carbon $to = null): array
    {
        return [
            'name' => 'clusters_pending',
            'value' => $this->clustersPendingValue(),
            'range_label' => $this->formatRangeLabel($from, $to),
        ];
    }

    public function clustersPendingValue(): int
    {
        return Area::whereHas('parcels', fn ($q) => $q->where('status', 'sorted'))->count();
    }

    /**
     * 3. Riders on duty = active AND checked in today.
     */
    public function ridersOnDuty(): array
    {
        return [
            'name' => 'riders_on_duty',
            'value' => $this->ridersOnDutyValue(),
            'range_label' => 'Today',
        ];
    }

    public function ridersOnDutyValue(): int
    {
        return Rider::where('is_active', true)
            ->whereNotNull('checked_in_at')
            ->whereDate('checked_in_at', Carbon::today())
            ->count();
    }

    /**
     * 4. Riders active = total active rider accounts (is_active = true).
     */
    public function ridersActive(): array
    {
        return [
            'name' => 'riders_active',
            'value' => $this->ridersActiveValue(),
            'range_label' => 'Active Pool',
        ];
    }

    public function ridersActiveValue(): int
    {
        return Rider::where('is_active', true)->count();
    }

    /**
     * 5. Riders in route = on-duty riders with at least one out_for_delivery parcel.
     */
    public function ridersInRoute(): array
    {
        return [
            'name' => 'riders_in_route',
            'value' => $this->ridersInRouteValue(),
            'range_label' => 'Today',
        ];
    }

    public function ridersInRouteValue(): int
    {
        return Rider::where('is_active', true)
            ->whereNotNull('checked_in_at')
            ->whereDate('checked_in_at', Carbon::today())
            ->whereHas('parcels', fn ($q) => $q->where('status', 'out_for_delivery'))
            ->count();
    }

    /**
     * 6. Riders idle = on-duty riders with 0 out_for_delivery parcels.
     * By definition: idle + in_route = on_duty.
     */
    public function ridersIdle(): array
    {
        return [
            'name' => 'riders_idle',
            'value' => $this->ridersIdleValue(),
            'range_label' => 'Today',
        ];
    }

    public function ridersIdleValue(): int
    {
        return max(0, $this->ridersOnDutyValue() - $this->ridersInRouteValue());
    }

    /**
     * 7. Total parcels received today.
     */
    public function parcelsTotalToday(): array
    {
        return [
            'name' => 'parcels_total_today',
            'value' => $this->parcelsTotalTodayValue(),
            'range_label' => 'Today',
        ];
    }

    public function parcelsTotalTodayValue(): int
    {
        return Parcel::whereDate('received_at', Carbon::today())->count();
    }

    /**
     * 8. Total parcels within an explicit date range (defaults to start of month to today).
     */
    public function parcelsTotalRange(?Carbon $from = null, ?Carbon $to = null): array
    {
        $f = $from ? $from->copy()->startOfDay() : Carbon::today()->startOfMonth();
        $t = $to ? $to->copy()->endOfDay() : Carbon::today()->endOfDay();

        return [
            'name' => 'parcels_total_range',
            'value' => $this->parcelsTotalRangeValue($f, $t),
            'range_label' => $this->formatRangeLabel($f, $t),
        ];
    }

    public function parcelsTotalRangeValue(?Carbon $from = null, ?Carbon $to = null): int
    {
        $f = $from ? $from->copy()->startOfDay() : Carbon::today()->startOfMonth();
        $t = $to ? $to->copy()->endOfDay() : Carbon::today()->endOfDay();

        return Parcel::whereBetween('received_at', [$f, $t])->count();
    }

    /**
     * 9. Overdue parcel count (status = 'failed').
     */
    public function overdueCount(): array
    {
        return [
            'name' => 'overdue_count',
            'value' => $this->overdueCountValue(),
            'range_label' => 'Today',
        ];
    }

    public function overdueCountValue(): int
    {
        return Parcel::overdue()->count();
    }

    /**
     * Return all 9 canonical metrics as a keyed dictionary.
     */
    public function getCanonicalMetrics(?Carbon $from = null, ?Carbon $to = null): array
    {
        return [
            'parcels_in_queue' => $this->parcelsInQueue($from, $to),
            'clusters_pending' => $this->clustersPending($from, $to),
            'riders_on_duty' => $this->ridersOnDuty(),
            'riders_active' => $this->ridersActive(),
            'riders_idle' => $this->ridersIdle(),
            'riders_in_route' => $this->ridersInRoute(),
            'parcels_total_today' => $this->parcelsTotalToday(),
            'parcels_total_range' => $this->parcelsTotalRange($from, $to),
            'overdue_count' => $this->overdueCount(),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GLOBAL VIEW COMPOSER DATA: $nav_counts and $attention
    // ─────────────────────────────────────────────────────────────────────────

    public function getNavCounts(): array
    {
        return [
            'incoming' => Parcel::where('status', 'dropped_off')->count(),
            'riders_pending' => RiderApplication::where('status', 'pending')->count(),
            'overdue' => $this->overdueCountValue(),
        ];
    }

    public function getAttention(): array
    {
        return [
            'overdue' => $this->overdueCountValue(),
            'unassigned_clusters' => Area::whereDoesntHave('rider', fn ($q) => $q->where('is_active', true))
                ->whereHas('parcels', fn ($q) => $q->whereIn('status', ['sorted', 'assigned']))
                ->count(),
            'pending_riders' => RiderApplication::where('status', 'pending')->count(),
            'unconfirmed_deliveries' => Parcel::where('status', 'delivered')->whereNull('buyer_confirmed_at')->count(),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AUXILIARY METRICS USED BY CONTROLLERS
    // ─────────────────────────────────────────────────────────────────────────

    public function parcelsReceivedYesterday(): int
    {
        return Parcel::whereDate('received_at', Carbon::yesterday())->count();
    }

    public function parcelsAwaitingSortValue(): int
    {
        return Parcel::where('status', 'awaiting_sort')->count();
    }

    public function parcelsAssignedToRidersValue(): int
    {
        return Parcel::whereIn('status', ['assigned', 'out_for_delivery'])->count();
    }

    public function parcelsDeliveredTodayValue(): int
    {
        return Parcel::where('status', 'delivered')
            ->whereDate('delivered_at', Carbon::today())
            ->count();
    }

    public function parcelsDeliveredAllValue(): int
    {
        return Parcel::where('status', 'delivered')->count();
    }

    public function priorityCount(int $minutes = 30): int
    {
        return Parcel::where('status', 'awaiting_sort')
            ->where('received_at', '<=', now()->subMinutes($minutes))
            ->count();
    }

    public function slaPercentage(): float
    {
        $todayTotal = $this->parcelsTotalTodayValue();
        $priority = $this->priorityCount();

        return $todayTotal > 0
            ? round(100 - ($priority / $todayTotal) * 100, 1)
            : 100.0;
    }

    public function onTimePercentage(): float
    {
        $delivered = $this->parcelsDeliveredAllValue();
        $failed = $this->overdueCountValue();
        $total = $delivered + $failed;

        return $total > 0 ? round(($delivered / $total) * 100, 1) : 100.0;
    }

    public function hourlyProcessed(?Carbon $date = null): array
    {
        $date = $date ?? Carbon::today();
        $byHour = Parcel::whereDate('received_at', $date)->get()->groupBy(fn ($p) => $p->received_at?->hour ?? 0);
        $hourly = [];
        for ($h = 0; $h < 24; $h++) {
            $hourly[] = ['hour' => $h, 'count' => $byHour->get($h, collect())->count()];
        }

        return $hourly;
    }

    public function statusBreakdown(): array
    {
        return [
            'Delivered' => $this->parcelsDeliveredAllValue(),
            'Out for Delivery' => Parcel::where('status', 'out_for_delivery')->count(),
            'In Transit' => Parcel::where('status', 'assigned')->count(),
            'To Sort' => Parcel::whereIn('status', ['dropped_off', 'awaiting_sort', 'sorted'])->count(),
        ];
    }

    /**
     * Array of objects format for Kiro: [{status, count}]
     *
     * @return array<int, array{status: string, count: int}>
     */
    public function statusBreakdownList(): array
    {
        return [
            ['status' => 'Delivered', 'count' => $this->parcelsDeliveredAllValue()],
            ['status' => 'Out for Delivery', 'count' => Parcel::where('status', 'out_for_delivery')->count()],
            ['status' => 'In Transit', 'count' => Parcel::where('status', 'assigned')->count()],
            ['status' => 'To Sort', 'count' => Parcel::whereIn('status', ['dropped_off', 'awaiting_sort', 'sorted'])->count()],
        ];
    }

    // Incoming Parcels
    public function parcelsAwaitingScanValue(): int
    {
        return Parcel::where('status', 'dropped_off')->count();
    }

    public function parcelsScannedTodayValue(): int
    {
        return Parcel::whereDate('sorted_at', Carbon::today())
            ->where('status', '!=', 'dropped_off')
            ->count();
    }

    public function ridersDroppingOffValue(): int
    {
        return Parcel::where('status', 'dropped_off')
            ->whereNotNull('dropped_off_by_rider_id')
            ->distinct('dropped_off_by_rider_id')
            ->count('dropped_off_by_rider_id');
    }

    public function incomingShiftRate(): int
    {
        $scannedToday = $this->parcelsScannedTodayValue();

        return (int) round($scannedToday / max(1, now()->hour - 6));
    }

    // Sort Parcels
    public function stagedParcelsValue(): int
    {
        return Parcel::whereIn('status', ['sorted', 'assigned'])->count();
    }

    public function assignedTodayValue(): int
    {
        return Parcel::where('status', 'assigned')
            ->whereDate('assigned_at', Carbon::today())
            ->count();
    }

    // Delivery Monitoring
    public function deliveryMonitoringCounts(): array
    {
        return [
            'all' => Parcel::whereIn('status', ['assigned', 'out_for_delivery', 'delivered', 'failed'])->count(),
            'assigned' => Parcel::where('status', 'assigned')->count(),
            'out' => Parcel::where('status', 'out_for_delivery')->count(),
            'delivered' => Parcel::where('status', 'delivered')->count(),
            'failed' => $this->overdueCountValue(),
            'archived' => Parcel::onlyTrashed()->count(),
        ];
    }

    public function deliveryRailCounts(): array
    {
        return [
            'sorted' => Parcel::where('status', 'sorted')->count(),
            'assigned' => Parcel::where('status', 'assigned')->count(),
            'out' => Parcel::where('status', 'out_for_delivery')->count(),
            'delivered' => Parcel::where('status', 'delivered')->count(),
            'pending_confirm' => Parcel::where('status', 'delivered')->whereNull('buyer_confirmed_at')->count(),
            'failed' => $this->overdueCountValue(),
        ];
    }

    // Rider Management
    public function coveredAreasCount(): int
    {
        return Area::whereHas('rider')->count();
    }

    public function unassignedAreasCount(): int
    {
        return Area::whereDoesntHave('rider')->count();
    }

    // Delivery Assignment
    public function readyToDispatchValue(): int
    {
        return Parcel::where('status', 'sorted')->count();
    }

    public function ridersWithLoadCount(): int
    {
        return Area::whereHas('rider')
            ->whereHas('parcels', fn ($q) => $q->where('status', 'sorted'))
            ->count();
    }

    public function avgRiderLoad(): float
    {
        $totalRiders = $this->coveredAreasCount();
        if ($totalRiders === 0) {
            return 0.0;
        }

        $sortedCount = $this->readyToDispatchValue();

        return round($sortedCount / $totalRiders, 1);
    }
}
