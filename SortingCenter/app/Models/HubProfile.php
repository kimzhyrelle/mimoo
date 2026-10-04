<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HubProfile extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'contact_email', 'contact_phone', 'address', 'coverage_note'];

    // There's no per-user login anymore, so "Account" is really one row of
    // hub-level settings. This gets-or-creates that single row.
    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1], [
            'name' => 'Laguna Hub',
            'contact_email' => 'hub@santacruzhub.ph',
            'contact_phone' => '(049) 000-0000',
            'address' => 'Santa Cruz, Laguna',
            'coverage_note' => 'Covers Santa Cruz, Laguna, sorted barangay by barangay.',
        ]);
    }
}
