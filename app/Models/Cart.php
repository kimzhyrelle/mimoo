<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
    ];

    /**
     * Get the buyer that owns the cart.
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the items in the cart.
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get or create cart for a buyer.
     */
    public static function getOrCreateForBuyer(int $buyerId): self
    {
        return self::firstOrCreate(['buyer_id' => $buyerId]);
    }

    /**
     * Get total item count in cart.
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->items()->sum('quantity');
    }

    /**
     * Get subtotal of all items in cart.
     */
    public function getSubtotalAttribute(): float
    {
        $subtotal = 0;

        foreach ($this->items as $item) {
            if ($item->product && !$item->product->trashed()) {
                $price = $item->product->sale_price ?? $item->product->price;
                $subtotal += ($price * $item->quantity);
            }
        }

        return $subtotal;
    }
}
