<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiderApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'vehicle_type', 'plate_no', 'or_cr_verified',
        'license_status', 'applied_on', 'status', 'approved_rider_id',
    ];

    protected function casts(): array
    {
        return [
            'or_cr_verified' => 'boolean',
            'applied_on' => 'date',
        ];
    }

    public function approvedRider()
    {
        return $this->belongsTo(Rider::class, 'approved_rider_id');
    }

    public function docsLabel(): string
    {
        $orCr = $this->or_cr_verified ? 'OR/CR ✓' : 'OR/CR pending';
        $license = $this->license_status === 'verified' ? 'License ✓' : 'License pending';

        return "{$orCr} · {$license}";
    }
}
