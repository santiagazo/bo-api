<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyBrandProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('company'));
    }

    public function rules(): array
    {
        $hexColor = ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'];

        return [
            'logo_url' => ['sometimes', 'nullable', 'url:http,https', 'max:2048'],
            'icon_url' => ['sometimes', 'nullable', 'url:http,https', 'max:2048'],
            'primary_color' => ['sometimes', ...$hexColor],
            'secondary_color' => ['sometimes', ...$hexColor],
            'accent_color' => ['sometimes', ...$hexColor],
            'tagline' => ['sometimes', 'nullable', 'string', 'max:255'],
            'university_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'mission' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'vision' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ];
    }
}
