<?php

namespace App\Services;

use InvalidArgumentException;

class PhoneService
{
    /**
     * Format phone number to E.164 format for US/CA.
     *
     * Input examples that work:
     *   - "4803091250" -> "+14803091250"
     *   - "(480) 309-1250" -> "+14803091250"
     *   - "1-480-309-1250" -> "+14803091250"
     *   - "+14803091250" -> "+14803091250"
     *
     * @param  string  $phone  The phone number in any format
     * @param  string  $countryCode  The ISO country code (US or CA)
     * @return string|null The E.164 formatted number or null if invalid
     *
     * @throws InvalidArgumentException If country code is not US or CA
     */
    public function formatToE164(string $phone, string $countryCode = 'US'): ?string
    {
        $countryCode = strtoupper($countryCode);

        if ($countryCode !== 'US' && $countryCode !== 'CA') {
            throw new InvalidArgumentException('Only US and CA phone numbers are supported.');
        }

        // Strip all non-numeric characters
        $digitsOnly = preg_replace('/[^0-9]/', '', $phone);

        // If it's 11 characters and starts with 1, remove it
        if (strlen($digitsOnly) === 11 && str_starts_with($digitsOnly, '1')) {
            $digitsOnly = substr($digitsOnly, 1);
        }

        // Must be exactly 10 digits after normalization
        if (strlen($digitsOnly) !== 10) {
            return null;
        }

        // For US and CA, both use +1
        return '+1' . $digitsOnly;
    }

    /**
     * Check if a phone number is valid for US/CA.
     *
     * @param  string  $phone  The phone number in any format
     * @return bool
     */
    public function isValid(string $phone): bool
    {
        return $this->formatToE164($phone) !== null;
    }

    /**
     * Get the country calling code.
     *
     * @param  string  $countryCode  The ISO country code
     * @return string The calling code with +
     */
    public function getCountryCallingCode(string $countryCode = 'US'): string
    {
        $countryCode = strtoupper($countryCode);

        if ($countryCode !== 'US' && $countryCode !== 'CA') {
            throw new InvalidArgumentException('Only US and CA are supported.');
        }

        return '+1';
    }

    /**
     * Format for display (national format).
     * "+14803091250" -> "(480) 309-1250"
     *
     * @param  string  $e164Phone  Phone number in E.164 format
     * @return string|null Formatted phone or null if invalid
     */
    public function formatForDisplay(string $e164Phone): ?string
    {
        // Strip to digits only
        $digitsOnly = preg_replace('/[^0-9]/', '', $e164Phone);

        // Remove leading 1 if present
        if (strlen($digitsOnly) === 11 && str_starts_with($digitsOnly, '1')) {
            $digitsOnly = substr($digitsOnly, 1);
        }

        if (strlen($digitsOnly) !== 10) {
            return null;
        }

        // Format as (XXX) XXX-XXXX
        return sprintf(
            '(%s) %s-%s',
            substr($digitsOnly, 0, 3),
            substr($digitsOnly, 3, 3),
            substr($digitsOnly, 6, 4)
        );
    }
}



