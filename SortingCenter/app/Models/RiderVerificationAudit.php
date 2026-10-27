<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiderVerificationAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'rider_id',
        'rider_application_id',
        'action',
        'reason',
        'performed_by',
    ];

    public function rider()
    {
        return $this->belongsTo(Rider::class);
    }

    public function application()
    {
        return $this->belongsTo(RiderApplication::class, 'rider_application_id');
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
