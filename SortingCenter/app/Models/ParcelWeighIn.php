<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParcelWeighIn extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'parcel_id', 'user_id', 'weight_kg', 'recorded_at'];

    protected function casts(): array
    {
        return [
            'weight_kg' => 'decimal:2',
            'recorded_at' => 'datetime',
        ];
    }

    public function parcel(): BelongsTo
    {
        return $this->belongsTo(Parcel::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
