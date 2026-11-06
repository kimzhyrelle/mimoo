<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Voucher extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'min_purchase',
        'max_discount',
        'usage_limit',
        'usage_per_user',
        'usage_count',
        'valid_from',
        'valid_until',
        'status',
        'terms_conditions',
        'implemented_by',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_purchase' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'terms_conditions' => 'array',
    ];

    // Relationships
    public function usages(): HasMany
    {
        return $this->hasMany(VoucherUsage::class);
    }

    // Check if voucher is valid
    public function isValid(): bool
    {
        $now = Carbon::now();
        
        return $this->status === 'active' 
            && $now->between($this->valid_from, $this->valid_until)
            && ($this->usage_limit === null || $this->usage_count < $this->usage_limit);
    }

    // Check if user can use this voucher
    public function canBeUsedBy(User $user): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        $userUsageCount = $this->usages()
            ->where('user_id', $user->id)
            ->count();

        return $userUsageCount < $this->usage_per_user;
    }

    // Calculate discount amount
    public function calculateDiscount(float $orderTotal): float
    {
        if ($orderTotal < $this->min_purchase) {
            return 0;
        }

        if ($this->type === 'percentage') {
            $discount = $orderTotal * ($this->value / 100);
            
            // Apply max discount cap if set
            if ($this->max_discount !== null) {
                $discount = min($discount, $this->max_discount);
            }
            
            return round($discount, 2);
        }

        // Fixed discount
        return min($this->value, $orderTotal);
    }

    // Mark as used
    public function markAsUsed(User $user, int $orderId, float $discountAmount): void
    {
        VoucherUsage::create([
            'voucher_id' => $this->id,
            'user_id' => $user->id,
            'order_id' => $orderId,
            'discount_amount' => $discountAmount,
            'used_at' => now(),
        ]);

        $this->increment('usage_count');

        // Check if voucher should be marked as expired
        if ($this->usage_limit !== null && $this->usage_count >= $this->usage_limit) {
            $this->update(['status' => 'expired']);
        }
    }

    // Auto-expire vouchers (called via scheduler)
    public static function expireOldVouchers(): void
    {
        self::where('status', 'active')
            ->where('valid_until', '<', now())
            ->update(['status' => 'expired']);
    }
}
