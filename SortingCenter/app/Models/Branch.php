<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'parent_id', 'type', 'code', 'name', 'address', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public const PREFIXES = [
        'branch' => 'BR',
        'hub' => 'HUB',
        'warehouse' => 'WH',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $branch) {
            if (empty($branch->code)) {
                $branch->code = static::nextCode($branch->type);
            }
        });
    }

    public static function nextCode(string $type): string
    {
        $prefix = self::PREFIXES[$type] ?? 'BR';
        $codes = static::withoutGlobalScopes()
            ->where('type', $type)
            ->pluck('code');

        $max = 0;
        foreach ($codes as $code) {
            if (preg_match('/^'.preg_quote($prefix, '/').'-(\d+)$/', (string) $code, $matches)) {
                $max = max($max, (int) $matches[1]);
            }
        }

        return $prefix.'-'.str_pad((string) ($max + 1), 3, '0', STR_PAD_LEFT);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function label(): string
    {
        return $this->code.' · '.$this->name;
    }

    public function typeLabel(): string
    {
        return ucfirst($this->type);
    }
}
