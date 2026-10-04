<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rider extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'initials', 'vehicle_type', 'plate_no', 'area_id', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function parcels()
    {
        return $this->hasMany(Parcel::class);
    }

    // Label shown as the pill on the dashboard (e.g. "Out for delivery")
    public function statusLabel(): string
    {
        return match ($this->status) {
            'available' => 'Available',
            'busy' => 'Out for delivery',
            default => 'Off shift',
        };
    }

    // "On shift" toggle used by Rider Management (busy still counts as on shift)
    public function isActive(): bool
    {
        return $this->status !== 'offline';
    }

    public function vehicleLabel(): string
    {
        return trim($this->vehicle_type.($this->plate_no ? ' · '.$this->plate_no : ''));
    }
}
