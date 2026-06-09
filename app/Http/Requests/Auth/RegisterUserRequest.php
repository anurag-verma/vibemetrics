<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Rules\PersonName;
use App\Rules\Timezone;
use App\Support\TimezoneList;
use App\Support\ValidationHelpers;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class RegisterUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', new PersonName],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'timezone' => ['nullable', 'string', 'max:64', new Timezone],
        ];
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->has('name')) {
            $merge['name'] = ValidationHelpers::normalizePersonName($this->input('name'));
        }

        if ($this->has('timezone')) {
            $merge['timezone'] = TimezoneList::resolve($this->input('timezone'));
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }
}
