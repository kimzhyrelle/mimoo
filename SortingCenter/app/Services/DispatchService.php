<?php

namespace App\Services;

use App\Models\Parcel;
use App\Models\ParcelStatusLog;
use App\Models\Rider;
use App\Models\User;
use App\Support\CompanyContext;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DispatchService
{
    public const MAX_ACTIVE_JOBS = 3;

    public const ACTIVE_STATUSES = ['assigned', 'picked_up', 'out_for_delivery'];

    public function __construct(
        private ShipmentStatusService $statusService,
        private AuditLogger $audit,
    ) {}

    public function activeJobs(Rider $rider): int
    {
        return $rider->parcels()->whereIn('status', self::ACTIVE_STATUSES)->count();
    }

    public function assertEligible(Rider $rider): void
    {
        if (! $rider->isActive()) {
            throw ValidationException::withMessages([
                'rider_id' => "Rider {$rider->name} is not active and cannot be assigned parcels.",
            ]);
        }

        if (! $rider->isOnline()) {
            throw ValidationException::withMessages([
                'rider_id' => "Rider {$rider->name} is offline and cannot be assigned parcels.",
            ]);
        }

        if ($this->activeJobs($rider) >= self::MAX_ACTIVE_JOBS) {
            throw ValidationException::withMessages([
                'rider_id' => "Rider {$rider->name} already holds 3 active jobs. Free a slot first.",
            ]);
        }
    }

    public function eligibleRiders(?int $areaId = null): Collection
    {
        return Rider::query()
            ->where('is_active', true)
            ->where('status', '!=', 'suspended')
            ->whereNotNull('checked_in_at')
            ->whereDate('checked_in_at', today())
            ->when($areaId, fn ($q) => $q->orderByRaw('CASE WHEN area_id = ? THEN 0 ELSE 1 END', [$areaId]))
            ->orderBy('name')
            ->get()
            ->filter(fn (Rider $rider) => $this->activeJobs($rider) < self::MAX_ACTIVE_JOBS)
            ->values();
    }

    public function autoAssign(Parcel $parcel): ?Rider
    {
        $this->assertAssignableParcel($parcel);

        $rider = $this->eligibleRiders($parcel->area_id)->first();

        if (! $rider) {
            return null;
        }

        $this->assign($parcel, $rider, null, 'Auto-assigned to nearest eligible rider');

        return $rider;
    }

    public function assign(Parcel $parcel, Rider $rider, ?User $user = null, ?string $reason = null): Parcel
    {
        $this->assertAssignableParcel($parcel);
        $this->assertEligible($rider);

        if ($parcel->company_id !== $rider->company_id) {
            throw ValidationException::withMessages([
                'rider_id' => 'Rider belongs to another company.',
            ]);
        }

        DB::transaction(function () use ($parcel, $rider, $user, $reason) {
            $parcel->update(['rider_id' => $rider->id]);
            $this->statusService->transition($parcel->fresh(), 'assigned', $reason ?? "Assigned to {$rider->name}", $user);
        });

        return $parcel->fresh();
    }

    /**
     * @param  array<int, int>  $parcelIds
     * @return array{assigned: int, skipped: array<int, array{id: int, tracking_no: string, reason: string}>}
     */
    public function bulkAssign(array $parcelIds, Rider $rider, ?User $user = null): array
    {
        $this->assertEligible($rider);

        $assigned = 0;
        $skipped = [];
        $remaining = self::MAX_ACTIVE_JOBS - $this->activeJobs($rider);

        $parcels = Parcel::whereIn('id', $parcelIds)->orderBy('id')->get();

        foreach ($parcels as $parcel) {
            if ($remaining <= 0) {
                $skipped[] = ['id' => $parcel->id, 'tracking_no' => $parcel->tracking_no, 'reason' => 'Rider reached the 3-job limit.'];

                continue;
            }

            try {
                $this->assertAssignableParcel($parcel);
                $this->assign($parcel, $rider, $user, 'Bulk assigned');
                $remaining--;
                $assigned++;
            } catch (ValidationException $e) {
                $skipped[] = [
                    'id' => $parcel->id,
                    'tracking_no' => $parcel->tracking_no,
                    'reason' => $e->validator->errors()->first(),
                ];
            }
        }

        return ['assigned' => $assigned, 'skipped' => $skipped];
    }

    public function reassign(Parcel $parcel, Rider $rider, ?User $user = null): Parcel
    {
        if (in_array($parcel->status, ['picked_up', 'out_for_delivery', 'delivered', 'returned', 'returned_to_sender', 'lost'], true)) {
            throw ValidationException::withMessages([
                'parcel_id' => 'Reassignment is only allowed before the parcel is picked up.',
            ]);
        }

        $oldRider = $parcel->rider?->name ?? 'Unassigned';
        $from = $parcel->status;
        $this->assertEligible($rider);

        if ($parcel->company_id !== $rider->company_id) {
            throw ValidationException::withMessages([
                'rider_id' => 'Rider belongs to another company.',
            ]);
        }

        $parcel->update(['rider_id' => $rider->id, 'status' => 'assigned', 'assigned_at' => now()]);

        ParcelStatusLog::create([
            'company_id' => $parcel->company_id,
            'parcel_id' => $parcel->id,
            'from' => $from,
            'to' => 'assigned',
            'user_id' => $user?->id ?? auth()->id(),
            'reason' => "Reassigned from {$oldRider} to {$rider->name}",
        ]);

        $this->audit->log('dispatch.reassign', $parcel, ['rider' => $oldRider], ['rider' => $rider->name], "Reassigned to {$rider->name}", $user?->id);

        return $parcel->fresh();
    }

    /**
     * Suspending a rider returns every not-yet-picked-up job to the Unassigned
     * queue. Picked-up parcels stay with the rider.
     *
     * @return int number of parcels returned to the queue
     */
    public function suspend(Rider $rider, string $reason, ?User $user = null): int
    {
        $this->assertReason($reason);

        $returned = 0;

        DB::transaction(function () use ($rider, $reason, $user, &$returned) {
            $jobs = $rider->parcels()->whereIn('status', ['assigned'])->get();

            foreach ($jobs as $parcel) {
                $from = $parcel->status;
                $parcel->update([
                    'rider_id' => null,
                    'status' => 'ready_for_pickup',
                    'assigned_at' => null,
                ]);

                ParcelStatusLog::create([
                    'company_id' => $parcel->company_id,
                    'parcel_id' => $parcel->id,
                    'from' => $from,
                    'to' => 'ready_for_pickup',
                    'user_id' => $user?->id ?? auth()->id(),
                    'reason' => 'Rider suspended — returned to Unassigned queue',
                ]);

                $returned++;
            }

            $rider->update([
                'status' => 'suspended',
                'is_active' => false,
                'checked_in_at' => null,
                'suspended_at' => now(),
                'suspension_reason' => $reason,
            ]);

            $this->audit->log('rider.suspended', $rider, ['status' => 'active'], ['status' => 'suspended'], $reason, $user?->id);
        });

        return $returned;
    }

    public function reactivate(Rider $rider, string $reason, ?User $user = null): void
    {
        $this->assertReason($reason);

        $rider->update([
            'status' => 'available',
            'is_active' => true,
            'checked_in_at' => now(),
            'suspended_at' => null,
            'suspension_reason' => null,
        ]);

        $this->audit->log('rider.reactivated', $rider, ['status' => 'suspended'], ['status' => 'active'], $reason, $user?->id);
    }

    private function assertAssignableParcel(Parcel $parcel): void
    {
        if ($parcel->company_id !== CompanyContext::id()) {
            abort(404);
        }

        if ($parcel->rider_id && in_array($parcel->status, self::ACTIVE_STATUSES, true)) {
            throw ValidationException::withMessages([
                'parcel_id' => "Parcel {$parcel->tracking_no} already has a rider.",
            ]);
        }

        if ($parcel->isTerminal()) {
            throw ValidationException::withMessages([
                'parcel_id' => "Parcel {$parcel->tracking_no} is already {$parcel->statusLabel()}.",
            ]);
        }

        if (! $parcel->area_id) {
            throw ValidationException::withMessages([
                'parcel_id' => "Parcel {$parcel->tracking_no} has no barangay. Determine the area first.",
            ]);
        }
    }

    private function assertReason(string $reason): void
    {
        $length = mb_strlen(trim($reason));

        if ($length < 10 || $length > 300) {
            throw ValidationException::withMessages([
                'reason' => 'Give a reason between 10 and 300 characters.',
            ]);
        }
    }
}
