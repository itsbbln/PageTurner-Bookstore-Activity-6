<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IsbnRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $raw = $this->coerceExcelNumberToString($value);
        $raw = strtoupper(trim($raw));

        // keep digits and X only, strip spaces/hyphens/other punctuation
        $raw = preg_replace('/[^0-9X]/', '', $raw) ?? '';

        // Length-only validation (no checksum), per requirement.
        // - ISBN-10: 10 chars, digits with optional X as last char
        // - ISBN-13: 13 digits
        if ($this->looksLikeIsbn10($raw) || $this->looksLikeIsbn13($raw)) {
            return;
        }

        $fail('The ISBN must be 10 digits (optionally ending with X) or 13 digits.');
    }

    private function coerceExcelNumberToString(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_int($value)) {
            return (string) $value;
        }

        if (is_float($value)) {
            return sprintf('%.0f', $value);
        }

        $s = trim((string) $value);
        if ($s === '') {
            return '';
        }

        // Scientific notation string like 9.78123E+12
        if (preg_match('/^[0-9]+(\\.[0-9]+)?E\\+?[0-9]+$/i', $s)) {
            return sprintf('%.0f', (float) $s);
        }

        return $s;
    }

    private function looksLikeIsbn10(string $s): bool
    {
        return (bool) preg_match('/^[0-9]{9}[0-9X]$/', $s);
    }

    private function looksLikeIsbn13(string $s): bool
    {
        return (bool) preg_match('/^[0-9]{13}$/', $s);
    }
}

