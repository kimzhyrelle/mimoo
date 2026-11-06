<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidName implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $trimmed = trim($value);
        
        // Check length
        if (mb_strlen($trimmed) < 2) {
            $fail('The :attribute must be at least 2 characters.');
            return;
        }
        
        if (mb_strlen($trimmed) > 50) {
            $fail('The :attribute must not exceed 50 characters.');
            return;
        }
        
        // Allow letters (including ñ/Ñ), spaces, hyphen, apostrophe, period
        if (!preg_match('/^[a-zA-ZñÑ\s\-\'.]+$/u', $trimmed)) {
            $fail('Only letters are allowed.');
            return;
        }
    }
}
