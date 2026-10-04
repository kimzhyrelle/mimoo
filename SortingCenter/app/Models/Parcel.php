<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parcel extends Model
{
    use HasFactory;

    protected $fillable = [
        'tracking_no', 'address', 'seller_name', 'area_id', 'rider_id',
        'dropped_off_by_rider_id', 'status', 'carrier', 'scanned_undelivered',
        'eta_note', 'received_at', 'sorted_at', 'assigned_at', 'delivered_at',
        'buyer_confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
            'sorted_at' => 'datetime',
            'assigned_at' => 'datetime',
            'delivered_at' => 'datetime',
            'buyer_confirmed_at' => 'datetime',
            'scanned_undelivered' => 'boolean',
        ];
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

    // Minutes waited since being received, used by the "Needs attention" table
    public function waitingMinutes(): int
    {
        return $this->received_at ? $this->received_at->diffInMinutes(now()) : 0;
    }
}
