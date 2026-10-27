<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Area;
use App\Models\Parcel;
use App\Models\ParcelStatusLog;
use App\Models\Rider;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssignmentService
{
    public function __construct(
        protected ParcelStatusService $statusService
    ) {}

    /**
     * Validate that a rider is eligible to receive assignments.
     * Enforces active and on-duty requirements.
     *
     * @throws ValidationException
     */
    public function validateRiderForAssignment(Rider|int $rider): Rider
    {
        if (is_int($rider)) {
            $rider = Rider::findOrFail($rider);
        }

        if (! $rider->isActive()) {
            throw ValidationException::withMessages([
                'rider_id' => ["Rider {$rider->name} is inactive and cannot be assigned parcels."],
            ]);
        }

        if (! $rider->isOnDuty()) {
            throw ValidationException::withMessages([
                'rider_id' => ["Rider {$rider->name} is off duty and cannot be assigned parcels."],
            ]);
        }

        return $rider;
    }

    /**
     * Assign a single parcel to a rider.
     *
     * @throws ValidationException
     */
    public function assignParcel(Parcel $parcel, Rider|int $rider, ?User $user = null): array
    {
        if ($parcel->isDelivered()) {
            throw ValidationException::withMessages([
                'parcel_id' => ["Parcel {$parcel->tracking_no} is already delivered and cannot be reassigned."],
            ]);
        }

        if (! $parcel->area_id) {
            throw ValidationException::withMessages([
                'parcel_id' => ["Parcel {$parcel->tracking_no} has no barangay assigned. Determine barangay first."],
            ]);
        }

        $riderModel = $this->validateRiderForAssignment($rider);

        return DB::transaction(function () use ($parcel, $riderModel, $user) {
            $parcel->update(['rider_id' => $riderModel->id]);

            $result = $this->statusService->transition(
                parcel: $parcel,
                newStatus: 'assigned',
                reason: "Assigned to {$riderModel->name}",
                user: $user
            );

            return [
                'ok' => true,
                'message' => "Parcel {$parcel->tracking_no} assigned to {$riderModel->name}.",
                'undo_token' => $result['undo_token'] ?? null,
                'parcel' => $parcel->fresh(['area', 'rider']),
            ];
        });
    }

    /**
     * Bulk assign an array of parcel IDs to a rider in a single transaction.
     *
     * @param  int[]  $parcelIds
     *
     * @throws ValidationException
     */
    public function bulkAssign(array $parcelIds, Rider|int $rider, ?User $user = null): array
    {
        $riderModel = $this->validateRiderForAssignment($rider);

        $parcels = Parcel::whereIn('id', $parcelIds)->get();

        if ($parcels->isEmpty()) {
            throw ValidationException::withMessages([
                'parcel_ids' => ['No valid parcels found for assignment.'],
            ]);
        }

        // Validate all parcels before modifying any
        foreach ($parcels as $parcel) {
            if ($parcel->isDelivered()) {
                throw ValidationException::withMessages([
                    'parcel_ids' => ["Parcel {$parcel->tracking_no} is already delivered and cannot be reassigned."],
                ]);
            }
            if (! $parcel->area_id) {
                throw ValidationException::withMessages([
                    'parcel_ids' => ["Parcel {$parcel->tracking_no} has no barangay assigned."],
                ]);
            }
        }

        return DB::transaction(function () use ($parcels, $riderModel, $user) {
            $assignedCount = 0;
            $updatedParcels = [];

            foreach ($parcels as $parcel) {
                $parcel->update(['rider_id' => $riderModel->id]);

                // Transition status to assigned if not already
                if ($parcel->status !== 'assigned') {
                    $this->statusService->transition(
                        parcel: $parcel,
                        newStatus: 'assigned',
                        reason: "Bulk assigned to {$riderModel->name}",
                        user: $user
                    );
                } else {
                    ParcelStatusLog::create([
                        'parcel_id' => $parcel->id,
                        'from' => 'assigned',
                        'to' => 'assigned',
                        'user_id' => $user?->id ?? auth()->id(),
                        'reason' => "Reassigned to {$riderModel->name}",
                    ]);
                }

                $assignedCount++;
                $updatedParcels[] = $parcel->fresh(['area', 'rider']);
            }

            return [
                'ok' => true,
                'message' => "{$assignedCount} ".($assignedCount === 1 ? 'parcel' : 'parcels')." assigned to {$riderModel->name}.",
                'count' => $assignedCount,
                'rider' => $riderModel,
                'parcels' => $updatedParcels,
            ];
        });
    }

    /**
     * Auto-cluster awaiting_sort parcels into barangays.
     * Returns structured result {sorted, needs_manual_barangay[]}.
     */
    public function autoCluster(): array
    {
        $parcels = Parcel::where('status', 'awaiting_sort')->get();
        $sortedIds = [];
        $unmatchedIds = [];

        foreach ($parcels as $parcel) {
            $area = Area::matchForAddress($parcel->address);

            if (! $area) {
                $unmatchedIds[] = $parcel->id;

                continue;
            }

            $parcel->update(['area_id' => $area->id]);
            $this->statusService->transition($parcel, 'sorted', "Auto-clustered to {$area->name}");
            $sortedIds[] = $parcel->id;
        }

        $count = count($sortedIds);
        $unmatchedCount = count($unmatchedIds);

        return [
            'ok' => true,
            'sorted' => $sortedIds,
            'needs_manual_barangay' => $unmatchedIds,
            'count' => $count,
            'unmatched_count' => $unmatchedCount,
            'message' => "Auto-cluster sorted {$count} ".($count === 1 ? 'parcel' : 'parcels')
                .($unmatchedCount > 0 ? " — {$unmatchedCount} need a manual barangay." : '.'),
        ];
    }

    /**
     * Sync routes & riders: check for open parcels before route unassign or deactivation.
     * If rider holds open parcels and reassignToRiderId is missing, returns blocked status (409).
     * If reassignToRiderId is provided, moves open parcels atomically in a transaction.
     */
    public function reassignOrBlock(Rider $rider, ?int $reassignToRiderId = null): ?array
    {
        $openParcels = $rider->parcels()
            ->whereIn('status', ['assigned', 'out_for_delivery'])
            ->get();

        if ($openParcels->isEmpty()) {
            return null;
        }

        if (! $reassignToRiderId) {
            return [
                'blocked' => true,
                'count' => $openParcels->count(),
                'open_parcels' => $openParcels->map(fn ($p) => [
                    'id' => $p->id,
                    'tracking_no' => $p->tracking_no,
                    'status' => $p->status,
                    'address' => $p->address,
                ])->values()->all(),
                'message' => "Rider {$rider->name} holds {$openParcels->count()} open ".($openParcels->count() === 1 ? 'parcel' : 'parcels').'. Reassign them first or provide reassign_to.',
            ];
        }

        if ($reassignToRiderId === $rider->id) {
            throw ValidationException::withMessages([
                'reassign_to' => ['Cannot reassign parcels to the same rider being unassigned.'],
            ]);
        }

        $newRider = $this->validateRiderForAssignment($reassignToRiderId);

        return DB::transaction(function () use ($openParcels, $rider, $newRider) {
            foreach ($openParcels as $parcel) {
                $parcel->update(['rider_id' => $newRider->id]);

                ParcelStatusLog::create([
                    'parcel_id' => $parcel->id,
                    'from' => $parcel->status,
                    'to' => $parcel->status,
                    'user_id' => auth()->id(),
                    'reason' => "Reassigned from {$rider->name} to {$newRider->name}",
                ]);
            }

            Activity::create([
                'rider_id' => $newRider->id,
                'icon' => 'violet',
                'description' => "Transferred <b>{$openParcels->count()} parcels</b> from {$rider->name} to {$newRider->name}",
            ]);

            return [
                'reassigned' => true,
                'count' => $openParcels->count(),
                'new_rider' => $newRider,
                'message' => "Reassigned {$openParcels->count()} open parcels from {$rider->name} to {$newRider->name}.",
            ];
        });
    }

    /**
     * Return rider load formatted for dropdowns: [{id, label, open_parcels, status, disabled}].
     *
     * @return array<int, array{id: int, label: string, open_parcels: int, status: string, disabled: bool}>
     */
    public function getRidersLoad(): array
    {
        $riders = Rider::with('area')
            ->withCount(['parcels as open_parcels' => fn ($q) => $q->whereIn('status', ['assigned', 'out_for_delivery'])])
            ->orderBy('name')
            ->get();

        return $riders->map(function (Rider $r) {
            $disabled = ! $r->isActive() || ! $r->isOnDuty();
            $areaName = $r->area?->name ?? 'Unassigned';

            return [
                'id' => $r->id,
                'label' => "{$r->name} ({$r->initials}) — {$areaName}".($disabled ? ' (Unavailable)' : ''),
                'open_parcels' => (int) $r->open_parcels,
                'status' => $r->statusLabel(),
                'disabled' => $disabled,
            ];
        })->values()->all();
    }

    /**
     * Dispatch board execution with preview dry-run support.
     * When preview=true, returns what will change without writing to DB.
     */
    public function dispatchAll(bool $preview = false): array
    {
        $areas = Area::with('rider')->get();

        $previewData = [];
        $unassignedClusters = [];
        $totalParcels = 0;

        foreach ($areas as $area) {
            $stagedParcels = Parcel::where('area_id', $area->id)
                ->where('status', 'sorted')
                ->get();

            if ($stagedParcels->isEmpty()) {
                continue;
            }

            if (! $area->rider || ! $area->rider->isActive()) {
                $unassignedClusters[] = [
                    'area_id' => $area->id,
                    'area_name' => $area->name,
                    'count' => $stagedParcels->count(),
                    'reason' => $area->rider ? 'Assigned rider is inactive' : 'No courier assigned',
                ];

                continue;
            }

            $previewData[] = [
                'area_id' => $area->id,
                'area_name' => $area->name,
                'rider_id' => $area->rider->id,
                'rider_name' => $area->rider->name,
                'parcels_count' => $stagedParcels->count(),
                'parcel_ids' => $stagedParcels->pluck('id')->all(),
            ];

            $totalParcels += $stagedParcels->count();
        }

        if ($preview) {
            return [
                'ok' => true,
                'preview' => true,
                'will_change' => $totalParcels > 0,
                'parcels_count' => $totalParcels,
                'areas' => $previewData,
                'unassigned_clusters' => $unassignedClusters,
                'message' => "Preview: {$totalParcels} parcels ready to push across ".count($previewData).' assigned clusters.',
            ];
        }

        // Commit dispatch
        return DB::transaction(function () use ($previewData, $unassignedClusters) {
            $dispatchedCount = 0;

            foreach ($previewData as $item) {
                Parcel::whereIn('id', $item['parcel_ids'])
                    ->update([
                        'status' => 'assigned',
                        'rider_id' => $item['rider_id'],
                        'assigned_at' => now(),
                    ]);

                foreach ($item['parcel_ids'] as $pId) {
                    ParcelStatusLog::create([
                        'parcel_id' => $pId,
                        'from' => 'sorted',
                        'to' => 'assigned',
                        'user_id' => auth()->id(),
                        'reason' => "Dispatched to loading docks for {$item['rider_name']}",
                    ]);
                }

                $dispatchedCount += $item['parcels_count'];
            }

            if ($dispatchedCount > 0) {
                Activity::create([
                    'icon' => 'green',
                    'description' => "Pushed <b>{$dispatchedCount} parcels</b> across ".count($previewData).' clusters to Loading Docks',
                ]);
            }

            return [
                'ok' => true,
                'preview' => false,
                'dispatched_count' => $dispatchedCount,
                'clusters_dispatched' => count($previewData),
                'unassigned_clusters' => $unassignedClusters,
                'message' => "Successfully dispatched {$dispatchedCount} parcels across ".count($previewData).' clusters.',
            ];
        });
    }

    /**
     * Detect clusters with sorted parcels but no assigned/active courier.
     *
     * @return Collection<int, Area>
     */
    public function getUnassignedClusters(): Collection
    {
        return Area::whereDoesntHave('rider', fn ($q) => $q->where('is_active', true))
            ->whereHas('parcels', fn ($q) => $q->whereIn('status', ['sorted', 'assigned']))
            ->get();
    }

    /**
     * Pickup schedule: sorted chronologically by cutoff, with courier assignment flag.
     *
     * @return Collection<int, Area>
     */
    public function getPickupSchedule(): Collection
    {
        return Area::with('rider')
            ->whereNotNull('cutoff_time')
            ->get()
            ->sortBy(function (Area $area) {
                try {
                    return Carbon::parse($area->cutoff_time)->format('H:i');
                } catch (\Throwable) {
                    return $area->cutoff_time;
                }
            })
            ->values()
            ->map(function (Area $area) {
                $area->has_no_courier = ! $area->rider || ! $area->rider->isActive();

                return $area;
            });
    }
}
