<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CollectEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'tracking_id' => ['required', 'uuid'],
            'name'        => ['required', 'string', 'max:100'],
            'url'         => ['nullable', 'string', 'url', 'max:2048'],
            'visitor_id'  => ['nullable', 'uuid'],
            'props'       => ['nullable', 'array', 'max:20'],
            'props.*'     => ['nullable'],
        ];
    }
}
