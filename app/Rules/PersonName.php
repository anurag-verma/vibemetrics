<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PersonName implements ValidationRule
{
    /** Letters (Unicode), apostrophes, and hyphens; one or more name parts. */
    private const PATTERN = '/^[\p{L}\'\-]+(?: [\p{L}\'\-]+)*$/u';

    private const DISALLOWED = '/[\d]|[^\p{L}\s\'\-]/u';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $name = (string) $value;

        if ($name === '' || mb_strlen($name) > 255) {
            $fail('Please enter your name.');

            return;
        }

        if (preg_match(self::DISALLOWED, $name)) {
            $fail('Please remove any numbers or special symbols from your name.');

            return;
        }

        if (! preg_match(self::PATTERN, $name)) {
            $fail('Please enter your name.');
        }
    }
}
