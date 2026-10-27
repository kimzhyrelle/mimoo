<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HubProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'contact_email', 'contact_phone', 'address', 'coverage_note',
        'staff_name', 'staff_initials', 'staff_title',
        'first_name', 'last_name', 'middle_initial', 'birthday',
    ];

    protected $casts = [
        'birthday' => 'date',
    ];

    // There's no per-user login anymore, so "Account" is really one row of
    // hub-level settings. This gets-or-creates that single row.
    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1], [
            'name' => config('sorting.hub_name', 'Laguna Hub (Santa Cruz)'),
            'contact_email' => 'hub@santacruzhub.ph',
            'contact_phone' => '(049) 000-0000',
            'address' => 'Santa Cruz, Laguna',
            'coverage_note' => 'Covers Santa Cruz, Laguna, sorted barangay by barangay.',
            'staff_name' => 'Sorting Staff',
            'staff_initials' => 'SC',
            'staff_title' => 'Sorting Staff',
        ]);
    }

    /** Derive 2-letter initials from a full name if staff_initials is blank */
    public function getDisplayInitialsAttribute(): string
    {
        if (! empty($this->staff_initials)) {
            return strtoupper(substr($this->staff_initials, 0, 2));
        }
        $words = explode(' ', trim($this->staff_name));

        return strtoupper(
            substr($words[0], 0, 1).(isset($words[1]) ? substr($words[1], 0, 1) : '')
        );
    }
}
