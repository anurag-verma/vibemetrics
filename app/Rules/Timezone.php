<?php

namespace App\Rules;

use App\Support\TimezoneList;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Timezone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! TimezoneList::isValid($value)) {
            $fail('The :attribute must be a valid timezone.');
        }
    }
}
