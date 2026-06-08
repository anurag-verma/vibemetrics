<?php

namespace App\Http\Requests;

use App\Rules\SiteName;
use App\Support\ValidationHelpers;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('site'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', new SiteName],
            'is_paused' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('name')) {
            $this->merge([
                'name' => ValidationHelpers::normalizeSiteName($this->input('name')),
            ]);
        }
    }
}
