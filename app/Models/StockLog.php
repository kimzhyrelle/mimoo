<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'change',
        'old_stock',
        'new_stock',
        'reason',
        'reference_id',
    ];

    protected $casts = [
        'change' => 'integer',
        'old_stock' => 'integer',
        'new_stock' => 'integer',
    ];

    /**
     * Get the product that owns the stock log.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Create a stock log entry for a product.
     */
    public static function log(
        int $productId,
        int $change,
        int $oldStock,
        int $newStock,
        string $reason,
        ?string $referenceId = null
    ): self {
        return self::create([
            'product_id' => $productId,
            'change' => $change,
            'old_stock' => $oldStock,
            'new_stock' => $newStock,
            'reason' => $reason,
            'reference_id' => $referenceId,
        ]);
    }
}
