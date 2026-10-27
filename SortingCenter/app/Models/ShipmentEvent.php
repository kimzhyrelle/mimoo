<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class ShipmentEvent extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'parcel_id', 'from_status', 'to_status', 'actor_role',
        'user_id', 'reason', 'meta', 'occurred_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function parcel()
    {
        return $this->belongsTo(Parcel::class);
    }
}
