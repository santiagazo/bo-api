<?php

namespace App\Http\Requests;

use App\Models\Organization;
use App\Rules\ValidPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Organization::class);
    }

    public function rules(): array
    {
        return [
            'display_name' => ['required', 'string', 'max:255'],
            'legal_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:50'],
            'website_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'primary_email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'primary_phone' => ['sometimes', 'nullable', 'string', 'max:20', new ValidPhoneNumber],
            'phone_country_code' => ['sometimes', 'nullable', 'string', 'in:US,CA'],
            'address_id' => ['sometimes', 'nullable', 'integer', 'exists:addresses,id'],
            'types' => ['sometimes', 'array'],
            'types.*.type' => ['required_with:types', 'string', 'max:100'],
            'types.*.is_primary' => ['sometimes', 'boolean'],
            'address' => ['sometimes', 'array'],
            'address.name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address.line1' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address.line2' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address.city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address.state' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address.postal_code' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address.country' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone_country_code.in' => 'Only US and CA phone numbers are supported.',
        ];
    }
}
