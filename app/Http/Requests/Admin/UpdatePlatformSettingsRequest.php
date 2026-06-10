<?php

namespace App\Http\Requests\Admin;

use App\Rules\DateRangePreset;
use App\Support\RichTextSanitizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlatformSettingsRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->input('support_email') === '') {
            $merge['support_email'] = null;
        }

        if ($this->input('announcement_link_url') === '') {
            $merge['announcement_link_url'] = null;
        }

        if ($this->input('announcement_link_label') === '') {
            $merge['announcement_link_label'] = null;
        }

        if ($this->has('announcement_message')) {
            $merge['announcement_message'] = RichTextSanitizer::sanitize((string) $this->input('announcement_message'));
        }

        if ($merge !== []) {
            $this->merge($merge);
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
            'default_date_range' => ['required', 'string', 'max:32', new DateRangePreset],
            'maintenance_mode' => ['required', 'boolean'],
            'app_display_name' => ['required', 'string', 'max:80'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'brand_primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'email_logo_same_as_site' => ['required', 'boolean'],
            'transactional_emails_enabled' => ['required', 'boolean'],
            'email_welcome_enabled' => ['required', 'boolean'],
            'email_password_changed_enabled' => ['required', 'boolean'],
            'email_account_deactivated_enabled' => ['required', 'boolean'],
            'announcement_enabled' => ['required', 'boolean'],
            'announcement_message' => [
                Rule::requiredIf(fn () => $this->boolean('announcement_enabled')),
                'nullable',
                'string',
                'max:4000',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (RichTextSanitizer::plainTextLength((string) $value) > 500) {
                        $fail('The announcement message must not exceed 500 characters.');
                    }
                },
            ],
            'announcement_type' => ['required', 'string', Rule::in(['info', 'warning', 'success'])],
            'announcement_audience' => ['required', 'string', Rule::in(['all', 'authenticated', 'users', 'admins'])],
            'announcement_link_url' => ['nullable', 'url', 'max:2048'],
            'announcement_link_label' => ['nullable', 'string', 'max:80'],
            'announcement_dismissible' => ['required', 'boolean'],
        ];
    }
}
