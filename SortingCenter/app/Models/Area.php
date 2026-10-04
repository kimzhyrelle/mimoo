<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'chart_color', 'pickup_window', 'cutoff_time', 'pickup_status'];

    public function parcels()
    {
        return $this->hasMany(Parcel::class);
    }

    public function riders()
    {
        return $this->hasMany(Rider::class);
    }

    // Business rule: at most one rider is assigned to a barangay at a time.
    public function rider()
    {
        return $this->hasOne(Rider::class);
    }
}
