<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DispatchManifest extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'generated_by', 'manifest'];

    protected function casts(): array
    {
        return [
            'manifest' => 'array',
        ];
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
