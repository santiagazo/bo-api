<?php

namespace App\Http\Requests;

use App\Enums\OrganizationType;
use App\Rules\ValidPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('organization'));
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'required', new Enum(OrganizationType::class)],
            'phone' => ['nullable', 'string', 'max:20', new ValidPhoneNumber],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'phone_country_code' => ['sometimes', 'nullable', 'string', 'in:US,CA'],
            'address_id' => ['nullable', 'integer', 'exists:addresses,id'],
            'referral_fee_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            
            // Address fields (for creating/updating inline)
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
            'referral_fee_percentage.max' => 'Referral fee percentage cannot exceed 100%.',
        ];
    }
}

