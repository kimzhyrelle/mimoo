<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PhilippineMobile implements ValidationRule
{
    /**
     * Normalize mobile number to 09XXXXXXXXX format
     */
    public static function normalize(string $value): string
    {
        $cleaned = preg_replace('/\s/', '', $value);
        
        // If starts with +639, convert to 09
        if (str_starts_with($cleaned, '+639')) {
            return '0' . substr($cleaned, 3);
        }
        
        // If starts with 639, convert to 09
        if (str_starts_with($cleaned, '639')) {
            return '0' . substr($cleaned, 2);
        }
        
        return $cleaned;
    }
    
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $normalized = self::normalize($value);
        
        // Must be exactly 11 digits starting with 09
        if (!preg_match('/^09\d{9}$/', $normalized)) {
            $fail('The :attribute must be a valid PH mobile number (09XXXXXXXXX or +639XXXXXXXXX).');
            return;
        }
    }
}
