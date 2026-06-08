<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DomainName implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $domain = strtolower(trim((string) $value));
        $domain = preg_replace('#^https?://#', '', $domain) ?? $domain;
        $domain = rtrim($domain, '/');

        if ($domain === '' || strlen($domain) > 255) {
            $fail('Enter a valid domain such as example.com.');

            return;
        }

        if (str_contains($domain, '/') || str_contains($domain, '@') || str_contains($domain, ' ')) {
            $fail('Enter a valid domain such as example.com.');

            return;
        }

        if ($domain === 'localhost') {
            return;
        }

        if (filter_var($domain, FILTER_VALIDATE_IP)) {
            return;
        }

        if (! filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            $fail('Enter a valid domain such as example.com.');
        }
    }
}
