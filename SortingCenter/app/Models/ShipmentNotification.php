<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class ShipmentNotification extends Model
{
    use BelongsToCompany;

    protected $table = 'shipment_notifications';

    protected $fillable = [
        'company_id', 'parcel_id', 'role', 'type', 'title', 'body', 'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function parcel()
    {
        return $this->belongsTo(Parcel::class);
    }
}
