<?php

namespace App\Rules;

use App\Support\AnalyticsDateRange;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DateRangePreset implements ValidationRule
{
    public function __construct(private bool $allowCustom = false) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The :attribute must be a valid date range preset.');

            return;
        }

        if ($value === 'custom' && ! $this->allowCustom) {
            $fail('The :attribute must be a valid date range preset.');

            return;
        }

        if (! AnalyticsDateRange::isValidPreset($value)) {
            $fail('The :attribute must be a valid date range preset.');
        }
    }
}
