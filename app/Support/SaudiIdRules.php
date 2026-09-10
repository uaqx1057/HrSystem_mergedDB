<?php

namespace App\Support;

/**
 * One place for the KSA identity-number formats so every form that captures
 * them (add / edit / invite employee, careers apply) validates identically:
 *   - Iqama       : exactly 10 digits, starts with 2
 *   - National ID : exactly 10 digits, starts with 1
 */
class SaudiIdRules
{
    public const IQAMA_REGEX = 'regex:/^2[0-9]{9}$/';
    public const NATIONAL_ID_REGEX = 'regex:/^1[0-9]{9}$/';

    public const IQAMA_MESSAGE = 'The iqama number must be exactly 10 digits and start with 2.';
    public const NATIONAL_ID_MESSAGE = 'The national ID must be exactly 10 digits and start with 1.';

    /** JS test literals (kept in sync with the regexes above). */
    public const IQAMA_JS = '/^2\\d{9}$/';
    public const NATIONAL_ID_JS = '/^1\\d{9}$/';

    /** @return array<int, string> */
    public static function iqama(bool $required = true): array
    {
        return array_values(array_filter([$required ? 'required' : 'nullable', self::IQAMA_REGEX]));
    }

    /** @return array<int, string> */
    public static function nationalId(bool $required = true): array
    {
        return array_values(array_filter([$required ? 'required' : 'nullable', self::NATIONAL_ID_REGEX]));
    }

    /** @return array<string, string> */
    public static function messages(): array
    {
        return [
            'iqama_no.regex' => self::IQAMA_MESSAGE,
            'national_id.regex' => self::NATIONAL_ID_MESSAGE,
        ];
    }
}
