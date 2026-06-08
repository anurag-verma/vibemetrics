<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SiteName implements ValidationRule
{
    private const PATTERN = '/^[\p{L}\p{N}\s\.\-\'&(),:+#]+$/u';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $name = trim((string) $value);

        if ($name === '' || mb_strlen($name) > 255) {
            $fail('Enter a valid site name using letters, numbers, and common punctuation only.');

            return;
        }

        if (! preg_match(self::PATTERN, $name)) {
            $fail('Enter a valid site name using letters, numbers, and common punctuation only.');
        }
    }
}
