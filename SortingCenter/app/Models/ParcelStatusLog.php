<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParcelStatusLog extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'company_id',
        'parcel_id',
        'from',
        'to',
        'user_id',
        'reason',
        'undo_token',
        'undone_at',
    ];

    protected function casts(): array
    {
        return [
            'undone_at' => 'datetime',
        ];
    }

    public function parcel()
    {
        return $this->belongsTo(Parcel::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isUndone(): bool
    {
        return $this->undone_at !== null;
    }

    public function canUndo(): bool
    {
        if ($this->isUndone()) {
            return false;
        }

        if ($this->created_at->lt(now()->subMinutes(10))) {
            return false;
        }

        return true;
    }
}
