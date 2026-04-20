<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates that a phone number can be formatted to E.164 for US/CA.
 *
 * Accepts various input formats:
 *   - "4803091250"
 *   - "(480) 309-1250"
 *   - "1-480-309-1250"
 *   - "+14803091250"
 *
 * All must resolve to exactly 10 digits (after removing country code).
 */
class ValidPhoneNumber implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return; // Let nullable/required rules handle empty values
        }

        // Strip all non-numeric characters
        $digitsOnly = preg_replace('/[^0-9]/', '', $value);

        // If it's 11 characters and starts with 1, remove it (country code)
        if (strlen($digitsOnly) === 11 && str_starts_with($digitsOnly, '1')) {
            $digitsOnly = substr($digitsOnly, 1);
        }

        // Must be exactly 10 digits
        if (strlen($digitsOnly) !== 10) {
            $fail('The :attribute must be a valid 10-digit phone number including area code.');

            return;
        }

        // First digit cannot be 0 or 1 (invalid area codes)
        $firstDigit = substr($digitsOnly, 0, 1);
        if ($firstDigit === '0' || $firstDigit === '1') {
            $fail('The :attribute has an invalid area code.');

            return;
        }
    }
}



