<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = ['parcel_id', 'rider_id', 'icon', 'description', 'read_at'];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function parcel()
    {
        return $this->belongsTo(Parcel::class);
    }

    public function rider()
    {
        return $this->belongsTo(Rider::class);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    public function markAsRead(): void
    {
        if ($this->read_at === null) {
            $this->update(['read_at' => now()]);
        }
    }

    /** Icon → Tailwind color classes for the notification dot */
    public function iconClasses(): string
    {
        return match ($this->icon) {
            'green'  => 'bg-emerald-100 text-emerald-600',
            'red'    => 'bg-red-100 text-red-600',
            'accent' => 'bg-purple-100 text-purple-600',
            'violet' => 'bg-indigo-100 text-indigo-600',
            default  => 'bg-muted text-muted-foreground',
        };
    }

    /** Icon → SVG path for the notification dot icon */
    public function iconSvg(): string
    {
        return match ($this->icon) {
            'green'  => '<polyline points="20 6 9 17 4 12"/>',
            'red'    => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
            'accent' => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
            default  => '<circle cx="12" cy="12" r="10"/>',
        };
    }
}
