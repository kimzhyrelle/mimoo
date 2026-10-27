<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_checklist_id', 'key', 'title', 'body', 'checked', 'note', 'photo_path',
    ];

    protected function casts(): array
    {
        return [
            'checked' => 'boolean',
        ];
    }

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(BranchChecklist::class, 'branch_checklist_id');
    }
}
