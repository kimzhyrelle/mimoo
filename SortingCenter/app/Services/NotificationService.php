<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Parcel;
use App\Models\RiderApplication;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Build the grouped bell notifications.
     *
     * @return array{notifications: array<int, array<string, mixed>>, unread_count: int}
     */
    public function payload(): array
    {
        $overdue = Parcel::query()
            ->overdue()
            ->whereNotIn('status', ['delivered', 'returned'])
            ->orderBy('sla_due_at')
            ->limit(5)
            ->get()
            ->map(fn (Parcel $parcel) => [
                'id' => null,
                'type' => 'overdue',
                'title' => $parcel->tracking_no.' is overdue',
                'description' => $parcel->tracking_no.' - '.$parcel->statusLabel().' ('.$parcel->overdueLabel().')',
                'url' => route('delivery.monitoring', ['q' => $parcel->tracking_no]),
                'created_at' => ($parcel->sla_due_at ?? $parcel->updated_at)?->toIso8601String(),
                'time' => $parcel->sla_due_at?->diffForHumans() ?? $parcel->updated_at?->diffForHumans(),
                'read' => false,
                'icon' => 'red',
                'icon_classes' => 'bg-red-100 text-red-600',
                'icon_svg' => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
            ]);

        $unassigned = Parcel::query()
            ->whereNull('rider_id')
            ->whereIn('status', ['dropped_off', 'awaiting_sort', 'sorted'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Parcel $parcel) => [
                'id' => null,
                'type' => 'unassigned',
                'title' => $parcel->tracking_no.' needs a rider',
                'description' => $parcel->tracking_no.' - '.$parcel->statusLabel().' for '.($parcel->area->name ?? 'unmapped area'),
                'url' => route('parcels.sort', ['q' => $parcel->tracking_no]),
                'created_at' => $parcel->created_at?->toIso8601String(),
                'time' => $parcel->created_at?->diffForHumans(),
                'read' => false,
                'icon' => 'accent',
                'icon_classes' => 'bg-blue-100 text-blue-600',
                'icon_svg' => '<path d="m21 8-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/>',
            ]);

        $kyc = RiderApplication::query()
            ->where('status', 'pending')
            ->where(function ($query) {
                $query->whereNull('kyc_status')->orWhere('kyc_status', '!=', 'verified');
            })
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (RiderApplication $application) => [
                'id' => null,
                'type' => 'kyc',
                'title' => $application->name.' KYC pending',
                'description' => $application->name.' - '.$application->kycStatusLabel().' ('.$application->docsLabel().')',
                'url' => route('riders.index'),
                'created_at' => $application->created_at?->toIso8601String(),
                'time' => $application->created_at?->diffForHumans(),
                'read' => false,
                'icon' => 'violet',
                'icon_classes' => 'bg-indigo-100 text-indigo-600',
                'icon_svg' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/>',
            ]);

        $general = Activity::query()
            ->with(['parcel', 'rider'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (Activity $activity) => [
                'id' => $activity->id,
                'type' => 'general',
                'title' => strip_tags($activity->description),
                'description' => $activity->description,
                'url' => route('dashboard'),
                'created_at' => $activity->created_at?->toIso8601String(),
                'time' => $activity->created_at?->diffForHumans(),
                'read' => $activity->read_at !== null,
                'icon' => $activity->icon,
                'icon_classes' => $activity->iconClasses(),
                'icon_svg' => $activity->iconSvg(),
            ]);

        $notifications = $overdue
            ->concat($unassigned)
            ->concat($kyc)
            ->concat($general)
            ->values()
            ->all();

        $actionable = $this->countActionable();

        return [
            'notifications' => $notifications,
            'unread_count' => Activity::unread()->count() + $actionable,
        ];
    }

    public function countActionable(): int
    {
        $overdue = Parcel::query()
            ->overdue()
            ->whereNotIn('status', ['delivered', 'returned'])
            ->count();

        $unassigned = Parcel::query()
            ->whereNull('rider_id')
            ->whereIn('status', ['dropped_off', 'awaiting_sort', 'sorted'])
            ->count();

        $kyc = RiderApplication::query()
            ->where('status', 'pending')
            ->where(function ($query) {
                $query->whereNull('kyc_status')->orWhere('kyc_status', '!=', 'verified');
            })
            ->count();

        return $overdue + $unassigned + $kyc;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function grouped(): Collection
    {
        return collect($this->payload()['notifications'])->groupBy('type');
    }
}
