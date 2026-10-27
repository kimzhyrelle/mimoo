<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SmsVerificationService
{
    public const OTP_TTL_MINUTES = 10;

    public const MAX_ATTEMPTS = 5;

    public const LOCK_MINUTES = 15;

    public const RESEND_SECONDS = 60;

    public function __construct(private SmsSender $sms) {}

    public function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '63') && strlen($digits) === 12) {
            return '0'.substr($digits, 2);
        }

        if (str_starts_with($digits, '9') && strlen($digits) === 10) {
            return '0'.$digits;
        }

        if (str_starts_with($digits, '639') && strlen($digits) === 12) {
            return '0'.substr($digits, 2);
        }

        return $digits;
    }

    public function isValidPhone(string $phone): bool
    {
        return (bool) preg_match('/^09\d{9}$/', $this->normalizePhone($phone));
    }

    /**
     * @return array{phone: string, code: string, expires_at: string}
     */
    public function send(Model $subject, string $phone): array
    {
        $normalized = $this->normalizePhone($phone);

        if (! preg_match('/^09\d{9}$/', $normalized)) {
            throw ValidationException::withMessages([
                'phone' => 'Enter a valid PH mobile number: 09XXXXXXXXX or +639XXXXXXXXX.',
            ]);
        }

        if ($subject->otp_sent_at !== null && $subject->otp_sent_at->gt(now()->subSeconds(self::RESEND_SECONDS))) {
            $wait = self::RESEND_SECONDS - (int) $subject->otp_sent_at->diffInSeconds(now());

            throw ValidationException::withMessages([
                'phone' => "Please wait {$wait} seconds before requesting a new code.",
            ]);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expires = now()->addMinutes(self::OTP_TTL_MINUTES);

        $subject->forceFill([
            'otp_hash' => Hash::make($code),
            'otp_expires_at' => $expires,
            'otp_attempts' => 0,
            'otp_locked_until' => null,
            'otp_sent_at' => now(),
        ])->save();

        $this->sms->send($normalized, "Your Mimoo verification code is {$code}. It expires in 10 minutes. Never share this code.");

        return ['phone' => $normalized, 'code' => $code, 'expires_at' => $expires->toIso8601String()];
    }

    public function verify(Model $subject, string $code): void
    {
        if ($subject->otp_locked_until !== null && $subject->otp_locked_until->isFuture()) {
            throw ValidationException::withMessages([
                'code' => 'Too many attempts. Try again after '.$subject->otp_locked_until->format('H:i').'.',
            ]);
        }

        if ($subject->otp_hash === null || $subject->otp_expires_at === null) {
            throw ValidationException::withMessages(['code' => 'Request a verification code first.']);
        }

        if ($subject->otp_expires_at->isPast()) {
            throw ValidationException::withMessages(['code' => 'The code has expired. Request a new one.']);
        }

        if (! Hash::check($code, $subject->otp_hash)) {
            $attempts = (int) $subject->otp_attempts + 1;
            $attributes = ['otp_attempts' => $attempts];

            if ($attempts >= self::MAX_ATTEMPTS) {
                $attributes['otp_locked_until'] = now()->addMinutes(self::LOCK_MINUTES);
            }

            $subject->forceFill($attributes)->save();

            throw ValidationException::withMessages([
                'code' => $attempts >= self::MAX_ATTEMPTS
                    ? 'Too many wrong codes. Verification is locked for 15 minutes.'
                    : 'That code is incorrect. '.(self::MAX_ATTEMPTS - $attempts).' tries left.',
            ]);
        }

        $subject->forceFill([
            'phone_verified_at' => now(),
            'otp_hash' => null,
            'otp_expires_at' => null,
            'otp_attempts' => 0,
            'otp_locked_until' => null,
        ])->save();
    }

    /**
     * Changing the phone number always invalidates the previous verification
     * and sends a fresh OTP to the new number.
     *
     * @return array{phone: string, code: string, expires_at: string}
     */
    public function changePhone(Model $subject, string $phone): array
    {
        $normalized = $this->normalizePhone($phone);

        if (! $this->isValidPhone($normalized)) {
            throw ValidationException::withMessages([
                'phone' => 'Enter a valid PH mobile number: 09XXXXXXXXX or +639XXXXXXXXX.',
            ]);
        }

        $subject->forceFill([
            'phone' => $normalized,
            'phone_verified_at' => null,
            'otp_sent_at' => null,
        ])->save();

        return $this->send($subject->fresh(), $normalized);
    }
}
