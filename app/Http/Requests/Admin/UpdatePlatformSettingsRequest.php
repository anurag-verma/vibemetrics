<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlatformSettingsRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->input('support_email') === '') {
            $this->merge(['support_email' => null]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'max_sites_per_user' => ['required', 'integer', 'min:1', 'max:100'],
            'retention_days' => ['required', 'integer', 'min:30', 'max:3650'],
            'rollup_enabled' => ['required', 'boolean'],
            'collect_rate_limit' => ['required', 'integer', 'min:10', 'max:1000'],
            'registration_enabled' => ['required', 'boolean'],
            'default_analytics_range' => ['required', 'integer', Rule::in([7, 30, 90])],
            'maintenance_mode' => ['required', 'boolean'],
            'app_display_name' => ['required', 'string', 'max:80'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'brand_primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'email_logo_same_as_site' => ['required', 'boolean'],
        ];
    }
}
