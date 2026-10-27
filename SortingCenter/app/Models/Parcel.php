<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\HasListFilters;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class Parcel extends Model
{
    use BelongsToCompany, HasFactory, HasListFilters, SoftDeletes;

    protected $fillable = [
        'company_id', 'branch_id', 'order_id', 'tracking_no', 'address', 'seller_name', 'area_id', 'rider_id',
        'dropped_off_by_rider_id', 'status', 'carrier', 'scanned_undelivered',
        'eta_note', 'received_at', 'sorted_at', 'assigned_at', 'delivered_at',
        'buyer_confirmed_at', 'buyer_name', 'buyer_phone', 'weight_kg',
        'archived_at', 'archived_by', 'sla_due_at', 'scan_count', 'last_scanned_at',
        'delivery_attempts', 'failure_reason', 'failure_reasons', 'delivery_otp',
        'delivery_otp_verified_at', 'cod_amount', 'cod_collected', 'cod_remitted', 'cod_remitted_at',
        'is_prepaid', 'shipping_zone_id', 'shipping_rate_id', 'chargeable_weight',
        'shipping_fee', 'lost_at', 'lost_reason', 'damaged_at', 'damage_photo_path',
        'damaged_reason', 'return_reason', 'returned_at', 'seller_received_at',
        'stock_restored_at', 'refunded_at', 'seller_earnings', 'pickup_fee', 'delivery_fee',
    ];

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
            'sorted_at' => 'datetime',
            'assigned_at' => 'datetime',
            'delivered_at' => 'datetime',
            'buyer_confirmed_at' => 'datetime',
            'archived_at' => 'datetime',
            'sla_due_at' => 'datetime',
            'last_scanned_at' => 'datetime',
            'scan_count' => 'integer',
            'scanned_undelivered' => 'boolean',
            'weight_kg' => 'float',
            'delivery_attempts' => 'integer',
            'failure_reasons' => 'array',
            'delivery_otp_verified_at' => 'datetime',
            'cod_amount' => 'decimal:2',
            'cod_collected' => 'decimal:2',
            'cod_remitted' => 'boolean',
            'cod_remitted_at' => 'datetime',
            'is_prepaid' => 'boolean',
            'chargeable_weight' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'lost_at' => 'datetime',
            'damaged_at' => 'datetime',
            'returned_at' => 'datetime',
            'seller_received_at' => 'datetime',
            'stock_restored_at' => 'datetime',
            'refunded_at' => 'datetime',
            'seller_earnings' => 'decimal:2',
            'pickup_fee' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function zone()
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }

    public function shippingRate()
    {
        return $this->belongsTo(ShippingRate::class, 'shipping_rate_id');
    }

    public function shipmentEvents()
    {
        return $this->hasMany(ShipmentEvent::class)->orderBy('id');
    }

    public function shipmentNotifications()
    {
        return $this->hasMany(ShipmentNotification::class);
    }

    public function isReturned(): bool
    {
        return in_array($this->status, ['returned', 'returned_to_sender'], true);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, ['delivered', 'returned', 'returned_to_sender', 'lost'], true);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function rider()
    {
        return $this->belongsTo(Rider::class);
    }

    // The pickup rider who brought this parcel in from the seller (Incoming Parcels)
    public function droppedOffBy()
    {
        return $this->belongsTo(Rider::class, 'dropped_off_by_rider_id');
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(ParcelStatusLog::class)->orderByDesc('id');
    }

    public function weighIns()
    {
        return $this->hasMany(ParcelWeighIn::class)->orderByDesc('recorded_at');
    }

    public function archivedBy()
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null || $this->trashed();
    }

    public function isOverdue(): bool
    {
        if (in_array($this->status, ['delivered', 'returned', 'returned_to_sender', 'lost', 'damaged'], true)) {
            return false;
        }

        if ($this->sla_due_at !== null) {
            return now()->gt($this->sla_due_at);
        }

        // Fallback for legacy seeded records if status is failed
        return $this->status === 'failed';
    }

    public function overdueMinutes(): int
    {
        if (! $this->isOverdue()) {
            return 0;
        }

        if ($this->sla_due_at) {
            return (int) $this->sla_due_at->diffInMinutes(now());
        }

        return $this->waitingMinutes();
    }

    public function overdueLabel(): string
    {
        $mins = $this->overdueMinutes();
        if ($mins <= 0) {
            return '';
        }

        $hours = intdiv($mins, 60);
        $remainder = $mins % 60;

        if ($hours > 0) {
            return "{$hours}h {$remainder}m over";
        }

        return "{$remainder}m over";
    }

    public function scopeOverdue($query)
    {
        return $query->where(function ($q) {
            $q->where(function ($sub) {
                $sub->whereNotNull('sla_due_at')
                    ->where('sla_due_at', '<', now())
                    ->whereNotIn('status', ['delivered', 'returned', 'returned_to_sender', 'lost', 'damaged']);
            })->orWhere(function ($sub) {
                $sub->whereNull('sla_due_at')
                    ->whereIn('status', ['failed', 'failed_delivery']);
            });
        });
    }

    public function expectedScans(): int
    {
        return config('sorting.expected_scans', 4);
    }

    public function effectiveScanCount(): int
    {
        $minForStatus = match ($this->status) {
            'delivered' => 4,
            'out_for_delivery', 'assigned' => 3,
            'sorted' => 2,
            default => 1,
        };

        return max($this->scan_count ?? 1, $minForStatus);
    }

    public function validateScanTimestamp(?Carbon $scannedAt = null): void
    {
        $scannedAt = $scannedAt ?? now();
        if ($this->received_at && $scannedAt->lt($this->received_at)) {
            throw ValidationException::withMessages([
                'last_scanned_at' => ['Last scan time cannot be earlier than received time.'],
            ]);
        }
    }

    /**
     * Compute chronological custody scan events.
     * Never shows 1/4 for out_for_delivery or delivered without accounting for missed scans.
     */
    public function custodyEvents(): Collection
    {
        $events = collect();

        if ($this->delivered_at) {
            $events->push([
                't' => $this->delivered_at,
                'title' => 'Delivered',
                'body' => 'Handed over to consignee'.($this->rider ? ' by '.$this->rider->name : ''),
                'missed' => false,
            ]);
        }

        if ($this->assigned_at || in_array($this->status, ['out_for_delivery', 'delivered'], true)) {
            $out = in_array($this->status, ['out_for_delivery', 'delivered'], true);
            $timestamp = $this->assigned_at ?? ($this->delivered_at ? $this->delivered_at->copy()->subMinutes(15) : now());
            $events->push([
                't' => $timestamp,
                'title' => $out ? 'Dispatched' : 'Assigned',
                'body' => ($out ? 'Out for delivery with ' : 'Assigned to ').($this->rider->name ?? 'courier'),
                'missed' => ! $this->assigned_at,
            ]);
        }

        if ($this->sorted_at || in_array($this->status, ['assigned', 'out_for_delivery', 'delivered'], true)) {
            $timestamp = $this->sorted_at ?? ($this->assigned_at ? $this->assigned_at->copy()->subMinutes(10) : now());
            $events->push([
                't' => $timestamp,
                'title' => 'Sorted',
                'body' => 'Allocated to '.self::binFor($this->area).($this->area ? ' ('.$this->area->name.')' : ''),
                'missed' => ! $this->sorted_at,
            ]);
        }

        if ($this->received_at || $this->status !== 'dropped_off') {
            $timestamp = $this->received_at ?? ($this->sorted_at ? $this->sorted_at->copy()->subMinutes(15) : now());
            $events->push([
                't' => $timestamp,
                'title' => 'Inbound Scan',
                'body' => 'Received at the hub dock',
                'missed' => ! $this->received_at,
            ]);
        }

        return $events->sortByDesc(fn ($e) => $e['t']->timestamp)->values();
    }

    // Minutes waited since being received, used by the "Needs attention" table
    public function waitingMinutes(): int
    {
        return $this->received_at ? $this->received_at->diffInMinutes(now()) : 0;
    }

    /** Human label for the parcel's current status. */
    public function statusLabel(): string
    {
        return $this->statusLabelFor($this->status);
    }

    public function statusLabelFor(?string $status): string
    {
        return match ($status) {
            'dropped_off' => 'Dropped Off',
            'awaiting_sort' => 'Pending Sort',
            'received' => 'Received',
            'ready_for_pickup' => 'Ready for Pickup',
            'sorted' => 'Sorted',
            'assigned' => 'Assigned',
            'picked_up' => 'Picked Up',
            'at_hub' => 'At Hub',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Delivered',
            'failed' => 'Failed Delivery',
            'failed_delivery' => 'Failed Delivery',
            'return_to_sender' => 'Return to Sender',
            'returned' => 'Returned to Sender',
            'returned_to_sender' => 'Returned to Sender',
            'lost' => 'Lost',
            'damaged' => 'Damaged',
            default => ucfirst(str_replace('_', ' ', (string) $this->status)),
        };
    }

    /** Pill colour class (see .pill-* in resources/css/app.css). */
    public function statusPill(): string
    {
        return match ($this->status) {
            'delivered' => 'pill-green',
            'awaiting_sort', 'dropped_off', 'received', 'ready_for_pickup' => 'pill-amber',
            'failed', 'failed_delivery', 'returned', 'returned_to_sender', 'lost', 'damaged' => 'pill-red',
            'return_to_sender' => 'pill-red',
            'at_hub', 'picked_up' => 'pill-blue',
            default => 'pill-blue',
        };
    }

    /** Where in the hub the parcel currently sits. */
    public function stationLabel(): string
    {
        return match ($this->status) {
            'dropped_off' => 'Inbound Dock',
            'awaiting_sort' => 'Inbound Sorter',
            'received' => 'Inbound Dock',
            'ready_for_pickup' => 'Pickup Bay',
            'sorted' => 'Staging Area',
            'assigned' => 'Dispatch Bay',
            'picked_up' => 'In Transit',
            'at_hub' => 'Hub Storage',
            'out_for_delivery' => 'On the Road',
            'delivered' => 'Delivered',
            'failed', 'failed_delivery' => 'Exception Hold',
            'return_to_sender' => 'Return Transit',
            'returned', 'returned_to_sender' => 'Return Center',
            'lost' => 'Lost',
            'damaged' => 'Damage Hold',
            default => '—',
        };
    }

    /** Bin code shown on the scan result, derived from the matched barangay. */
    public static function binFor(?Area $area): string
    {
        return $area ? 'BIN-'.strtoupper($area->code ?? 'GEN') : 'BIN-PENDING';
    }

    /**
     * @return array<int, string>
     */
    protected function listSearchable(): array
    {
        return ['tracking_no', 'buyer_name', 'seller_name', 'address', 'buyer_phone'];
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function listSearchRelations(): array
    {
        return [
            'rider' => ['name', 'initials', 'phone'],
            'area' => ['name', 'code'],
            'droppedOffBy' => ['name', 'initials'],
            'branch' => ['name', 'code'],
        ];
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    protected function listSortable(): array
    {
        return [
            'oldest' => ['received_at', 'asc'],
            'newest' => ['received_at', 'desc'],
            'seller' => ['seller_name', 'asc'],
            'buyer' => ['buyer_name', 'asc'],
            'heaviest' => ['weight_kg', 'desc'],
            'assigned_newest' => ['assigned_at', 'desc'],
            'assigned_oldest' => ['assigned_at', 'asc'],
            'status' => ['status', 'asc'],
        ];
    }

    protected function listRiderColumn(): string
    {
        return 'rider_id';
    }

    protected function listAreaColumn(): string
    {
        return 'area_id';
    }

    protected function listDateColumn(): string
    {
        return 'received_at';
    }
}
