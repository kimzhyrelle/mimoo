<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BranchChecklist extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'company_id', 'branch_key', 'title', 'station', 'shift_key', 'shift_label',
        'shift_date', 'submitted_at', 'submitted_by',
    ];

    protected function casts(): array
    {
        return [
            'shift_date' => 'date',
            'submitted_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(BranchChecklistItem::class)->orderBy('id');
    }

    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }

    public function isLocked(): bool
    {
        return $this->isSubmitted();
    }
}
