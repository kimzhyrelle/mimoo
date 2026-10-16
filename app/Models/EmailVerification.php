<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class EmailVerification extends Model
{
    protected $fillable = [
        'email',
        'code',
        'expires_at',
        'verified',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified' => 'boolean',
    ];

    /**
     * Check if the OTP code has expired (5 minutes validity)
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the OTP is still valid
     */
    public function isValid(): bool
    {
        return !$this->verified && !$this->isExpired();
    }

    /**
     * Generate a new 6-digit OTP code
     */
    public static function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create or update OTP for an email
     */
    public static function createForEmail(string $email): self
    {
        // Invalidate any existing OTPs for this email
        self::where('email', $email)
            ->where('verified', false)
            ->update(['verified' => true]); // Mark old ones as "used"

        return self::create([
            'email' => $email,
            'code' => self::generateCode(),
            'expires_at' => Carbon::now()->addMinutes(5),
            'verified' => false,
        ]);
    }

    /**
     * Find valid OTP for email and code
     */
    public static function findValid(string $email, string $code): ?self
    {
        return self::where('email', $email)
            ->where('code', $code)
            ->where('verified', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();
    }
}
