<?php

namespace App\Http\Requests\Admin;

use App\Services\BrandingService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadBrandingAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $type = $this->route('type');
        $isFavicon = $type === BrandingService::ASSET_FAVICON;

        return [
            'file' => [
                'required',
                'file',
                'max:512',
                Rule::when(
                    $isFavicon,
                    ['mimes:png,jpg,jpeg,webp,ico,svg'],
                    ['mimes:png,jpg,jpeg,webp,svg'],
                ),
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'file.max' => 'The file must not be larger than 512 KB.',
        ];
    }
}
