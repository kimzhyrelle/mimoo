<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class CodRemittance extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'rider_id', 'amount', 'collected', 'difference', 'status',
        'reference', 'confirmed_by', 'confirmed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'collected' => 'decimal:2',
        'difference' => 'decimal:2',
        'confirmed_at' => 'datetime',
    ];

    public function rider()
    {
        return $this->belongsTo(Rider::class);
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}
