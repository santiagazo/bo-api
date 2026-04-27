<?php

namespace App\Http\Requests;

use App\Models\Company;
use App\Rules\ValidPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('company'));
    }

    public function rules(): array
    {
        /** @var Company $company */
        $company = $this->route('company');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'legal_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', 'alpha_dash', Rule::unique('companies', 'slug')->ignore($company->id)],
            'status' => ['sometimes', 'required', 'string', 'max:50'],
            'primary_email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'primary_phone' => ['sometimes', 'nullable', 'string', new ValidPhoneNumber],
            'phone_country_code' => ['sometimes', 'string', 'in:US,CA'],
            'website_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'timezone' => ['sometimes', 'required', 'string', 'max:100'],
            'currency_code' => ['sometimes', 'required', 'string', 'size:3'],
            'default_locale' => ['sometimes', 'required', 'string', 'max:10'],
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
