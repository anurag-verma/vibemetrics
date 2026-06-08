<?php

namespace App\Http\Requests;

use App\Rules\DomainName;
use App\Rules\SiteName;
use App\Services\SiteLimitService;
use App\Support\ValidationHelpers;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreSiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return app(SiteLimitService::class)->canCreate($this->user());
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', new SiteName],
            'domain' => [
                'required',
                'string',
                'max:255',
                new DomainName,
                Rule::unique('sites', 'domain')->where(fn ($query) => $query->where('user_id', $this->user()->id)),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $siteLimit = app(SiteLimitService::class);

            if ($siteLimit->isUnlimited($this->user())) {
                return;
            }

            if (! $siteLimit->canCreate($this->user())) {
                $max = $siteLimit->maxFor($this->user());
                $validator->errors()->add('domain', "You have reached the maximum of {$max} sites.");
            }
        });
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('name')) {
            $this->merge([
                'name' => ValidationHelpers::normalizeSiteName($this->input('name')),
            ]);
        }

        if ($this->has('domain')) {
            $domain = strtolower(trim((string) $this->input('domain')));
            $domain = preg_replace('#^https?://#', '', $domain) ?? $domain;
            $domain = rtrim($domain, '/');

            $this->merge(['domain' => $domain]);
        }
    }

    public function messages(): array
    {
        return [
            'domain.unique' => 'You already track this domain.',
        ];
    }
}
