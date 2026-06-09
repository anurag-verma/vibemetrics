<?php

namespace App\Http\Requests;

use App\Rules\DateRangePreset;
use App\Rules\PersonName;
use App\Rules\Timezone;
use App\Support\ValidationHelpers;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', new PersonName],
            'timezone' => ['sometimes', 'required', 'string', 'max:64', new Timezone],
            'default_date_range' => ['sometimes', 'required', 'string', 'max:32', new DateRangePreset],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('name')) {
            $this->merge([
                'name' => ValidationHelpers::normalizePersonName($this->input('name')),
            ]);
        }
    }
}
