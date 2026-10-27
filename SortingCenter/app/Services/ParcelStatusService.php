<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Parcel;
use App\Models\ParcelStatusLog;
use App\Models\Rider;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ParcelStatusService
{
    /**
     * State machine allowed transitions map.
     */
    public const ALLOWED_TRANSITIONS = [
        'dropped_off' => ['awaiting_sort', 'sorted', 'failed'],
        'received' => ['awaiting_sort', 'sorted', 'failed'],
        'awaiting_sort' => ['sorted', 'failed'],
        'sorted' => ['assigned', 'at_hub', 'failed'],
        'assigned' => ['at_hub', 'out_for_delivery', 'sorted'],
        'at_hub' => ['out_for_delivery', 'assigned'],
        'out_for_delivery' => ['delivered', 'failed', 'returned', 'at_hub'],
        'failed' => ['at_hub', 'returned', 'out_for_delivery'],
        'returned' => [],
        'delivered' => [],
    ];

    /**
     * Check if a transition from one status to another is permitted.
     */
    public function canTransition(string $from, string $to): bool
    {
        $allowed = self::ALLOWED_TRANSITIONS[$from] ?? [];

        return in_array($to, $allowed, true);
    }

    /**
     * Return allowed transitions from a given status.
     *
     * @return string[]
     */
    public function allowedTransitions(string $status): array
    {
        return self::ALLOWED_TRANSITIONS[$status] ?? [];
    }

    /**
     * Transition a parcel to a new status.
     * Enforces delivered lock, active rider for out_for_delivery, and writes audit log.
     *
     * @throws ValidationException
     */
    public function transition(Parcel $parcel, string $newStatus, ?string $reason = null, ?User $user = null): array
    {
        // 1. Delivered lock
        if ($parcel->isDelivered()) {
            throw ValidationException::withMessages([
                'status' => ['Delivered parcels are locked and cannot change status or be modified.'],
            ]);
        }

        // 2. Archived check
        if ($parcel->isArchived()) {
            throw ValidationException::withMessages([
                'status' => ['Archived parcels cannot change status. Restore the parcel first.'],
            ]);
        }

        $currentStatus = $parcel->status;

        // If already at that status, return success without duplicate logging
        if ($currentStatus === $newStatus) {
            return [
                'ok' => true,
                'message' => "Parcel {$parcel->tracking_no} is already {$newStatus}.",
                'undo_token' => null,
                'parcel' => $parcel,
            ];
        }

        // 3. State machine validation
        if (! $this->canTransition($currentStatus, $newStatus)) {
            throw ValidationException::withMessages([
                'status' => ["Cannot transition parcel from '{$currentStatus}' to '{$newStatus}'."],
            ]);
        }

        // 4. Validate active rider assigned before transition to out_for_delivery
        if ($newStatus === 'out_for_delivery') {
            if (! $parcel->rider_id) {
                throw ValidationException::withMessages([
                    'status' => ['Cannot dispatch to out_for_delivery without an assigned rider.'],
                ]);
            }

            $rider = $parcel->rider ?? Rider::find($parcel->rider_id);
            if (! $rider || ! $rider->isActive()) {
                throw ValidationException::withMessages([
                    'status' => ['Cannot dispatch to out_for_delivery: assigned rider is inactive.'],
                ]);
            }
        }

        $scannedAt = now();
        if (! in_array($newStatus, ['awaiting_sort', 'received'], true)) {
            $parcel->validateScanTimestamp($scannedAt);
        }

        // Generate undo token
        $undoToken = Str::random(40);

        // Update timestamps & fields based on new status
        $attributes = ['status' => $newStatus];

        match ($newStatus) {
            'awaiting_sort', 'received' => $attributes['received_at'] = $parcel->received_at ?? $scannedAt,
            'sorted' => $attributes['sorted_at'] = $scannedAt,
            'assigned' => $attributes['assigned_at'] = $scannedAt,
            'out_for_delivery' => $attributes['eta_note'] = $parcel->eta_note ?? '18 min',
            'delivered' => [
                $attributes['delivered_at'] = $scannedAt,
                $attributes['eta_note'] = null,
            ],
            'failed' => $attributes['scanned_undelivered'] = true,
            default => null,
        };

        // If transitioning from assigned back to sorted (unassign)
        if ($currentStatus === 'assigned' && $newStatus === 'sorted') {
            $attributes['rider_id'] = null;
            $attributes['assigned_at'] = null;
        }

        // SLA logic: set sla_due_at if not set
        if (! $parcel->sla_due_at) {
            $slaHours = config('sorting.sla_hours', 24);
            $baseTime = $attributes['received_at'] ?? $parcel->received_at ?? $scannedAt;
            $attributes['sla_due_at'] = $baseTime->copy()->addHours($slaHours);
        }

        // Scan tracking
        $attributes['last_scanned_at'] = $scannedAt;
        $currentScanCount = $parcel->scan_count ?? 1;
        $minForStatus = match ($newStatus) {
            'delivered' => 4,
            'out_for_delivery', 'assigned' => 3,
            'sorted' => 2,
            default => 1,
        };
        $attributes['scan_count'] = max($currentScanCount + 1, $minForStatus);

        $parcel->update($attributes);

        // Write audit log
        $userId = $user?->id ?? auth()->id();
        $log = ParcelStatusLog::create([
            'parcel_id' => $parcel->id,
            'from' => $currentStatus,
            'to' => $newStatus,
            'user_id' => $userId,
            'reason' => $reason,
            'undo_token' => $undoToken,
        ]);

        // Sync with Activity model for backward compatibility
        $this->recordActivity($parcel, $currentStatus, $newStatus, $reason);

        return [
            'ok' => true,
            'message' => "Parcel {$parcel->tracking_no} status updated to {$parcel->statusLabel()}.",
            'undo_token' => $undoToken,
            'parcel' => $parcel->fresh(['area', 'rider']),
        ];
    }

    /**
     * Undo a status transition using the undo token within 10 minutes.
     *
     * @throws ValidationException
     */
    public function undo(string $undoToken): array
    {
        $log = ParcelStatusLog::where('undo_token', $undoToken)->first();

        if (! $log) {
            throw ValidationException::withMessages([
                'token' => ['Invalid or expired undo token.'],
            ]);
        }

        if ($log->isUndone()) {
            throw ValidationException::withMessages([
                'token' => ['This transition has already been undone.'],
            ]);
        }

        if ($log->created_at->lt(now()->subMinutes(10))) {
            throw ValidationException::withMessages([
                'token' => ['Undo window of 10 minutes has expired.'],
            ]);
        }

        $parcel = Parcel::withTrashed()->find($log->parcel_id);
        if (! $parcel) {
            throw ValidationException::withMessages([
                'token' => ['Parcel associated with this action no longer exists.'],
            ]);
        }

        // Verify no subsequent transitions occurred
        $hasSubsequent = ParcelStatusLog::where('parcel_id', $log->parcel_id)
            ->where('id', '>', $log->id)
            ->exists();

        if ($hasSubsequent || $parcel->status !== $log->to) {
            throw ValidationException::withMessages([
                'token' => ['Cannot undo: parcel has undergone subsequent status changes.'],
            ]);
        }

        $previousStatus = $log->from ?? 'dropped_off';

        // Revert attributes
        $attributes = ['status' => $previousStatus];

        if ($log->to === 'delivered') {
            $attributes['delivered_at'] = null;
        }
        if ($log->to === 'out_for_delivery') {
            $attributes['eta_note'] = null;
        }
        if ($log->to === 'sorted') {
            $attributes['sorted_at'] = null;
        }
        if ($log->to === 'assigned') {
            $attributes['assigned_at'] = null;
        }
        if ($log->to === 'failed') {
            $attributes['scanned_undelivered'] = false;
        }

        if (($parcel->scan_count ?? 1) > 1) {
            $attributes['scan_count'] = $parcel->scan_count - 1;
        }

        $parcel->update($attributes);

        // Mark current log undone
        $log->update(['undone_at' => now()]);

        // Record undo audit log
        ParcelStatusLog::create([
            'parcel_id' => $parcel->id,
            'from' => $log->to,
            'to' => $previousStatus,
            'user_id' => auth()->id(),
            'reason' => 'Undone transition from '.$log->to.' to '.$previousStatus,
        ]);

        return [
            'ok' => true,
            'message' => "Parcel {$parcel->tracking_no} status reverted to {$parcel->statusLabel()}.",
            'parcel' => $parcel->fresh(['area', 'rider']),
        ];
    }

    /**
     * Archive a parcel (soft delete with archive timestamp and user).
     */
    public function archive(Parcel $parcel, ?User $user = null): array
    {
        $userId = $user?->id ?? auth()->id();

        $parcel->update([
            'archived_at' => now(),
            'archived_by' => $userId,
        ]);

        $parcel->delete();

        ParcelStatusLog::create([
            'parcel_id' => $parcel->id,
            'from' => $parcel->status,
            'to' => 'archived',
            'user_id' => $userId,
            'reason' => 'Parcel archived',
        ]);

        return [
            'ok' => true,
            'message' => "Parcel {$parcel->tracking_no} archived.",
            'parcel' => $parcel,
        ];
    }

    /**
     * Restore an archived parcel.
     */
    public function restore(int|string|Parcel $parcel): array
    {
        if (! ($parcel instanceof Parcel)) {
            $parcel = Parcel::onlyTrashed()->findOrFail($parcel);
        }

        $parcel->restore();
        $parcel->update([
            'archived_at' => null,
            'archived_by' => null,
        ]);

        ParcelStatusLog::create([
            'parcel_id' => $parcel->id,
            'from' => 'archived',
            'to' => $parcel->status,
            'user_id' => auth()->id(),
            'reason' => 'Parcel restored from archive',
        ]);

        return [
            'ok' => true,
            'message' => "Parcel {$parcel->tracking_no} restored.",
            'parcel' => $parcel->fresh(['area', 'rider']),
        ];
    }

    /**
     * Activity feed formatted as [{icon, text, url, at}].
     *
     * @return array<int, array{icon: string, text: string, url: string, at: string}>
     */
    public function getActivityFeed(int $limit = 10): array
    {
        $feed = [];

        $logs = ParcelStatusLog::with(['parcel' => fn ($q) => $q->withTrashed(), 'user'])
            ->latest()
            ->limit($limit)
            ->get();

        foreach ($logs as $log) {
            $tracking = $log->parcel?->tracking_no ?? "#{$log->parcel_id}";
            $icon = match ($log->to) {
                'delivered' => 'check-circle',
                'failed', 'returned' => 'alert-circle',
                'out_for_delivery' => 'truck',
                'assigned' => 'user-check',
                'sorted' => 'layers',
                'archived' => 'archive',
                default => 'box',
            };

            $statusText = $log->to === 'archived' ? 'Archived' : ($log->parcel?->statusLabel() ?? ucfirst(str_replace('_', ' ', $log->to)));
            $reasonText = $log->reason ? " — {$log->reason}" : '';

            $feed[] = [
                'icon' => $icon,
                'text' => "Parcel {$tracking} moved to {$statusText}{$reasonText}",
                'url' => route('delivery.monitoring', ['q' => $tracking]),
                'at' => $log->created_at ? $log->created_at->diffForHumans() : 'just now',
            ];
        }

        // If we have fewer items than limit, supplement with existing Activity table entries
        if (count($feed) < $limit) {
            $remaining = $limit - count($feed);
            $legacyActivities = Activity::with('parcel')->latest()->limit($remaining)->get();

            foreach ($legacyActivities as $act) {
                $feed[] = [
                    'icon' => match ($act->icon) {
                        'green' => 'check-circle',
                        'red' => 'alert-circle',
                        'accent' => 'truck',
                        default => 'box',
                    },
                    'text' => strip_tags($act->description),
                    'url' => $act->parcel ? route('delivery.monitoring', ['q' => $act->parcel->tracking_no]) : route('dashboard'),
                    'at' => $act->created_at ? $act->created_at->diffForHumans() : 'just now',
                ];
            }
        }

        return $feed;
    }

    /**
     * Helper to write a legacy Activity record for notifications & view components.
     */
    protected function recordActivity(Parcel $parcel, string $from, string $to, ?string $reason): void
    {
        $riderName = $parcel->rider?->name;

        [$icon, $desc] = match ($to) {
            'out_for_delivery' => [
                'accent',
                "<b>{$parcel->tracking_no}</b> is now out for delivery".($riderName ? " with {$riderName}" : ''),
            ],
            'delivered' => [
                'green',
                "<b>{$parcel->tracking_no}</b> delivered successfully".($riderName ? " by {$riderName}" : ''),
            ],
            'failed' => [
                'red',
                "<b>{$parcel->tracking_no}</b> delivery exception".($reason ? " — {$reason}" : ''),
            ],
            'assigned' => [
                'violet',
                "<b>{$parcel->tracking_no}</b> assigned to ".($riderName ?? 'rider'),
            ],
            'sorted' => [
                'violet',
                "<b>{$parcel->tracking_no}</b> sorted to ".($parcel->area?->name ?? 'staging'),
            ],
            default => [
                'violet',
                "<b>{$parcel->tracking_no}</b> status updated to {$to}",
            ],
        };

        Activity::create([
            'parcel_id' => $parcel->id,
            'rider_id' => $parcel->rider_id,
            'icon' => $icon,
            'description' => $desc,
        ]);
    }
}
