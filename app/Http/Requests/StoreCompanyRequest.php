<?php

namespace App\Http\Requests;

use App\Models\Company;
use App\Rules\ValidPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Company::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:companies,slug'],
            'status' => ['required', 'string', 'max:50'],
            'primary_email' => ['nullable', 'email', 'max:255'],
            'primary_phone' => ['nullable', 'string', new ValidPhoneNumber],
            'phone_country_code' => ['sometimes', 'string', 'in:US,CA'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'timezone' => ['required', 'string', 'max:100'],
            'currency_code' => ['required', 'string', 'size:3'],
            'default_locale' => ['required', 'string', 'max:10'],
            'address_id' => ['nullable', 'integer', 'exists:addresses,id'],
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
}
