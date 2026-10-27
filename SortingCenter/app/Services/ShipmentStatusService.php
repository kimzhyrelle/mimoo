<?php

namespace App\Services;

use App\Models\Parcel;
use App\Models\ParcelStatusLog;
use App\Models\ShipmentEvent;
use App\Models\ShipmentNotification;
use App\Models\User;
use App\Support\CompanyContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ShipmentStatusService
{
    public const REASONS = ['Buyer not home', 'Wrong address', 'Refused'];

    public const FINAL = ['delivered', 'returned', 'returned_to_sender', 'lost'];

    public const ALLOWED = [
        'dropped_off' => ['awaiting_sort', 'ready_for_pickup', 'sorted', 'failed', 'failed_delivery'],
        'received' => ['awaiting_sort', 'ready_for_pickup', 'sorted'],
        'awaiting_sort' => ['ready_for_pickup', 'sorted', 'failed', 'failed_delivery'],
        'ready_for_pickup' => ['assigned'],
        'sorted' => ['assigned', 'at_hub', 'ready_for_pickup'],
        'assigned' => ['picked_up', 'at_hub', 'out_for_delivery', 'failed_delivery', 'failed', 'return_to_sender'],
        'picked_up' => ['at_hub', 'out_for_delivery', 'failed_delivery', 'failed'],
        'at_hub' => ['out_for_delivery', 'assigned'],
        'out_for_delivery' => ['delivered', 'failed_delivery', 'failed', 'return_to_sender', 'at_hub'],
        'failed' => ['out_for_delivery', 'at_hub', 'return_to_sender', 'failed_delivery'],
        'failed_delivery' => ['out_for_delivery', 'at_hub', 'return_to_sender', 'failed'],
        'return_to_sender' => ['returned', 'returned_to_sender'],
        'returned' => [],
        'returned_to_sender' => [],
        'delivered' => [],
        'lost' => [],
        'damaged' => ['out_for_delivery', 'return_to_sender'],
    ];

    public function canTransition(string $from, string $to): bool
    {
        if ($from === $to) {
            return true;
        }

        return in_array($to, self::ALLOWED[$from] ?? [], true);
    }

    public function transition(
        Parcel $parcel,
        string $to,
        ?string $reason = null,
        ?User $user = null,
        string $actorRole = 'logistics',
        bool $force = false,
    ): array {
        $from = $parcel->status;

        if ($from === $to) {
            return ['ok' => true, 'message' => "Parcel {$parcel->tracking_no} is already {$parcel->statusLabel()}.", 'parcel' => $parcel];
        }

        if (! $force && ! $this->canTransition($from, $to)) {
            throw ValidationException::withMessages([
                'status' => ["Cannot move parcel from '{$from}' to '{$to}'. Scans must follow the required order."],
            ]);
        }

        if (in_array($to, ['lost'], true)) {
            $this->requireReason($reason, 'reason');
        }

        if ($to === 'delivered' && $parcel->status === 'out_for_delivery' && $parcel->delivery_otp && $parcel->delivery_otp_verified_at === null) {
            // The OTP is recorded for the buyer; scanning the parcel at the door
            // by logistics is accepted. Verification is a buyer-side concern.
        }

        return DB::transaction(function () use ($parcel, $from, $to, $reason, $user, $actorRole, $force) {
            $attributes = ['status' => $to, 'last_scanned_at' => now()];

            switch ($to) {
                case 'ready_for_pickup':
                    $attributes['sorted_at'] = now();
                    break;
                case 'sorted':
                    $attributes['sorted_at'] = now();
                    break;
                case 'assigned':
                    $attributes['assigned_at'] = now();
                    break;
                case 'picked_up':
                    $attributes['pickup_fee'] = 30;
                    break;
                case 'at_hub':
                    $attributes['sorted_at'] = $parcel->sorted_at ?? now();
                    break;
                case 'out_for_delivery':
                    $attributes['delivery_otp'] = $parcel->delivery_otp ?: (string) random_int(100000, 999999);
                    $attributes['eta_note'] = $parcel->eta_note ?: '18 min';
                    break;
                case 'delivered':
                    $attributes['delivered_at'] = now();
                    $attributes['delivery_fee'] = 50;
                    $attributes['seller_earnings'] = $parcel->is_prepaid ? 0 : max(0, (float) $parcel->cod_amount);
                    if (! $parcel->is_prepaid && (float) $parcel->cod_amount > 0) {
                        $attributes['cod_collected'] = $parcel->cod_amount;
                    }
                    break;
                case 'failed':
                case 'failed_delivery':
                    $attributes['delivery_fee'] = 0;
                    $attributes['scanned_undelivered'] = true;
                    break;
                case 'return_to_sender':
                    $attributes['return_reason'] = $reason ?? $parcel->failure_reason;
                    break;
                case 'returned':
                case 'returned_to_sender':
                    $attributes['returned_at'] = now();
                    break;
                case 'lost':
                    $attributes['lost_at'] = now();
                    $attributes['lost_reason'] = $reason;
                    break;
            }

            if (! $parcel->sla_due_at) {
                $base = $parcel->received_at ?? now();
                $attributes['sla_due_at'] = $base->copy()->addHours((int) config('sorting.sla_hours', 24));
            }

            $parcel->update($attributes);
            $parcel->refresh();

            $log = ParcelStatusLog::create([
                'company_id' => $parcel->company_id ?: CompanyContext::id(),
                'parcel_id' => $parcel->id,
                'from' => $from,
                'to' => $to,
                'user_id' => $user?->id ?? auth()->id(),
                'reason' => $reason,
                'undo_token' => Str::random(40),
            ]);

            ShipmentEvent::create([
                'company_id' => $parcel->company_id ?: CompanyContext::id(),
                'parcel_id' => $parcel->id,
                'from_status' => $from,
                'to_status' => $to,
                'actor_role' => $actorRole,
                'user_id' => $user?->id ?? auth()->id(),
                'reason' => $reason,
                'meta' => ['forced' => $force],
                'occurred_at' => now(),
            ]);

            $this->notifyRoles($parcel, $from, $to, $reason);

            return [
                'ok' => true,
                'message' => "Parcel {$parcel->tracking_no} moved to {$parcel->statusLabel()}.",
                'undo_token' => $log->undo_token,
                'parcel' => $parcel->fresh(['area', 'rider', 'branch']),
            ];
        });
    }

    public function failDelivery(Parcel $parcel, string $reason, ?User $user = null): array
    {
        if (! in_array($reason, self::REASONS, true)) {
            throw ValidationException::withMessages([
                'reason' => 'Choose a failure reason: Buyer not home, Wrong address, or Refused.',
            ]);
        }

        $attempts = (int) $parcel->delivery_attempts + 1;
        $history = $parcel->failure_reasons ?? [];
        $history[] = ['reason' => $reason, 'at' => now()->toIso8601String(), 'attempt' => $attempts];

        $parcel->update([
            'delivery_attempts' => $attempts,
            'failure_reason' => $reason,
            'failure_reasons' => $history,
        ]);

        $target = ($reason === 'Refused' || $attempts >= 3) ? 'return_to_sender' : 'failed_delivery';

        $result = $this->transition($parcel->fresh(), $target, $reason, $user);
        $result['attempts'] = $attempts;
        $result['returning'] = $target === 'return_to_sender';

        return $result;
    }

    public function override(Parcel $parcel, string $to, string $reason, ?User $user = null): array
    {
        $this->requireReason($reason, 'reason');

        $result = $this->transition($parcel, $to, $reason, $user, 'logistics_override', true);
        app(AuditLogger::class)->log('shipment.override', $parcel, ['status' => $parcel->status], ['status' => $to], $reason, $user?->id);

        return $result;
    }

    public function markLost(Parcel $parcel, string $reason, ?User $user = null): array
    {
        $this->requireReason($reason, 'reason');
        $result = $this->transition($parcel, 'lost', $reason, $user, 'logistics', true);
        app(AuditLogger::class)->log('shipment.lost', $parcel, ['status' => 'previous'], ['status' => 'lost'], $reason, $user?->id);

        return $result;
    }

    public function markDamaged(Parcel $parcel, string $reason, string $photoPath, ?User $user = null): array
    {
        $this->requireReason($reason, 'reason');

        $parcel->update([
            'damaged_at' => now(),
            'damaged_reason' => $reason,
            'damage_photo_path' => $photoPath,
        ]);

        $result = $this->transition($parcel->fresh(), 'damaged', $reason, $user, 'logistics', true);
        app(AuditLogger::class)->log('shipment.damaged', $parcel, [], ['photo' => $photoPath], $reason, $user?->id);

        return $result;
    }

    public function confirmSellerReceipt(Parcel $parcel): array
    {
        if (! $parcel->isReturned()) {
            throw ValidationException::withMessages([
                'status' => 'Only parcels returned to the seller can be confirmed as received.',
            ]);
        }

        $parcel->update([
            'seller_received_at' => now(),
            'stock_restored_at' => now(),
            'refunded_at' => $parcel->is_prepaid ? now() : null,
            'seller_earnings' => 0,
        ]);

        app(AuditLogger::class)->log('rts.seller_received', $parcel, [], [
            'stock_restored_at' => $parcel->stock_restored_at?->toIso8601String(),
            'refunded_at' => $parcel->refunded_at?->toIso8601String(),
        ], 'Seller confirmed receipt of returned parcel');

        return ['ok' => true, 'parcel' => $parcel->fresh()];
    }

    public function confirmDeliveryOtp(Parcel $parcel, string $code): bool
    {
        if (! $parcel->delivery_otp || ! hash_equals((string) $parcel->delivery_otp, (string) $code)) {
            throw ValidationException::withMessages(['code' => 'That delivery code is incorrect.']);
        }

        $parcel->update(['delivery_otp_verified_at' => now()]);

        return true;
    }

    private function notifyRoles(Parcel $parcel, string $from, string $to, ?string $reason): void
    {
        $label = $parcel->statusLabel();
        $roles = [];

        if ($parcel->buyer_name || $parcel->buyer_phone) {
            $roles['buyer'] = 'Your parcel '.$parcel->tracking_no.' is now '.$label.'.';
        }

        if ($parcel->seller_name) {
            $roles['seller'] = 'Parcel '.$parcel->tracking_no.' is now '.$label.'.';
        }

        if ($parcel->rider_id) {
            $roles['rider'] = 'Parcel '.$parcel->tracking_no.' is now '.$label.'.';
        }

        foreach ($roles as $role => $body) {
            ShipmentNotification::create([
                'company_id' => $parcel->company_id ?: CompanyContext::id(),
                'parcel_id' => $parcel->id,
                'role' => $role,
                'type' => 'status_change',
                'title' => $parcel->tracking_no.' · '.$label,
                'body' => $body.($reason ? ' Reason: '.$reason : ''),
            ]);
        }
    }

    private function requireReason(?string $reason, string $field): void
    {
        $length = mb_strlen(trim((string) $reason));

        if ($length < 10 || $length > 300) {
            throw ValidationException::withMessages([
                $field => 'Give a reason between 10 and 300 characters.',
            ]);
        }
    }
}
