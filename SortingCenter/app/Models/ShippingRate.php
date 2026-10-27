<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class ShippingRate extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'shipping_zone_id', 'name', 'base_fee', 'per_kg_fee',
        'min_weight_kg', 'max_weight_kg', 'is_active', 'effective_from',
    ];

    protected $casts = [
        'base_fee' => 'decimal:2',
        'per_kg_fee' => 'decimal:2',
        'min_weight_kg' => 'decimal:2',
        'max_weight_kg' => 'decimal:2',
        'is_active' => 'boolean',
        'effective_from' => 'datetime',
    ];

    public function zone()
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }

    public static function roundUpWeight(float $weight): int
    {
        return max(1, (int) ceil($weight));
    }

    public function feeForWeight(float $weight): float
    {
        $kg = self::roundUpWeight($weight);

        return round((float) $this->base_fee + max(0, $kg - 1) * (float) $this->per_kg_fee, 2);
    }
}
