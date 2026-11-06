<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'total',
        'status',
        'payment_status',
        'payment_method',
        'shipping_name',
        'shipping_address',
        'shipping_phone',
        'idempotency_key',
        'waybill_number',
        'waybill_generated_at',
        'waybill_printed_at',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'waybill_generated_at' => 'datetime',
        'waybill_printed_at' => 'datetime',
    ];

    /**
     * Check if order can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        // Can only cancel if pending or paid (not packed, shipped, or completed)
        return in_array($this->status, ['pending', 'paid']);
    }

    /**
     * Check if order is valid (not cancelled or failed).
     */
    public function isValid(): bool
    {
        return !in_array($this->status, ['cancelled', 'failed']);
    }

    /**
     * Get the buyer that owns the order.
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the items for the order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Generate unique waybill number for this order.
     */
    public function generateWaybillNumber(): string
    {
        if ($this->waybill_number) {
            return $this->waybill_number;
        }

        // Format: MIMWB-YYYYMMDD-ORDERID-RANDOM
        // Example: MIMWB-20261103-00000123-A4F2
        $date = now()->format('Ymd');
        $orderId = str_pad($this->id, 8, '0', STR_PAD_LEFT);
        $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
        
        $waybillNumber = "MIMWB-{$date}-{$orderId}-{$random}";
        
        $this->update([
            'waybill_number' => $waybillNumber,
            'waybill_generated_at' => now(),
        ]);

        return $waybillNumber;
    }

    /**
     * Mark waybill as printed.
     */
    public function markWaybillPrinted(): void
    {
        if (!$this->waybill_printed_at) {
            $this->update(['waybill_printed_at' => now()]);
        }
    }

    /**
     * Check if order has waybill.
     */
    public function hasWaybill(): bool
    {
        return !is_null($this->waybill_number);
    }
}
