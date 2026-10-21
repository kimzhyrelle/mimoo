<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'reported_by',
        'reason',
        'description',
        'status',
        'reviewed_at',
        'admin_notes',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the product that was reported.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who reported the product.
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
