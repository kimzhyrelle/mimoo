<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = ['company_id', 'name', 'code', 'chart_color', 'pickup_window', 'cutoff_time', 'pickup_status'];

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

    public function hasNoCourier(): bool
    {
        return ! $this->rider || ! $this->rider->isActive();
    }

    public function scopeOrderedByCutoff($query)
    {
        return $query->whereNotNull('cutoff_time')->orderBy('cutoff_time');
    }

    /**
     * Match a free-text address against the hub's known barangays. The longest
     * name wins so "Poblacion II" is not swallowed by "Poblacion I".
     */
    public static function matchForAddress(?string $address): ?self
    {
        $haystack = strtolower((string) $address);

        return static::get()
            ->sortByDesc(fn (self $a) => strlen($a->name))
            ->first(fn (self $a) => str_contains($haystack, strtolower(str_replace('Brgy. ', '', $a->name))));
    }
}
