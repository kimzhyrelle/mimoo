<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'name', 'region', 'areas', 'is_serviceable'];

    protected $casts = [
        'areas' => 'array',
        'is_serviceable' => 'boolean',
    ];

    public function rates()
    {
        return $this->hasMany(ShippingRate::class);
    }
}
