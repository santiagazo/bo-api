<?php

namespace App\Http\Requests;

use App\Rules\ValidPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('customer'));
    }

    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'required', 'string', 'in:person,business'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],

            'addresses' => ['sometimes', 'array'],
            'addresses.*.id' => ['sometimes', 'integer'],
            'addresses.*.type' => ['required_with:addresses', 'string', 'in:mailing,billing,other'],
            'addresses.*.address1' => ['sometimes', 'nullable', 'string', 'max:255'],
            'addresses.*.address2' => ['sometimes', 'nullable', 'string', 'max:255'],
            'addresses.*.city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'addresses.*.state' => ['sometimes', 'nullable', 'string', 'max:255'],
            'addresses.*.postal_code' => ['sometimes', 'nullable', 'string', 'max:255'],
            'addresses.*.country' => ['sometimes', 'nullable', 'string', 'max:255'],
            'addresses.*.is_primary' => ['sometimes', 'boolean'],
            'addresses.*.is_active' => ['sometimes', 'boolean'],

            'contacts' => ['sometimes', 'array', 'min:1'],
            'contacts.*.id' => ['sometimes', 'integer'],
            'contacts.*.name' => ['required_with:contacts', 'string', 'max:255'],
            'contacts.*.title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'contacts.*.notes' => ['sometimes', 'nullable', 'string'],
            'contacts.*.is_primary' => ['sometimes', 'boolean'],
            'contacts.*.is_active' => ['sometimes', 'boolean'],

            'contacts.*.phone_numbers' => ['sometimes', 'array'],
            'contacts.*.phone_numbers.*.id' => ['sometimes', 'integer'],
            'contacts.*.phone_numbers.*.type' => ['required_with:contacts.*.phone_numbers', 'string', 'in:mobile,home,work,other'],
            'contacts.*.phone_numbers.*.phone_number' => ['required_with:contacts.*.phone_numbers', 'string', 'max:20', new ValidPhoneNumber],
            'contacts.*.phone_numbers.*.phone_country_code' => ['sometimes', 'nullable', 'string', 'in:US,CA'],
            'contacts.*.phone_numbers.*.is_primary' => ['sometimes', 'boolean'],
            'contacts.*.phone_numbers.*.is_active' => ['sometimes', 'boolean'],

            'contacts.*.email_addresses' => ['sometimes', 'array'],
            'contacts.*.email_addresses.*.id' => ['sometimes', 'integer'],
            'contacts.*.email_addresses.*.type' => ['required_with:contacts.*.email_addresses', 'string', 'in:primary,secondary,other'],
            'contacts.*.email_addresses.*.email' => ['required_with:contacts.*.email_addresses', 'email', 'max:255'],
            'contacts.*.email_addresses.*.is_primary' => ['sometimes', 'boolean'],
            'contacts.*.email_addresses.*.is_active' => ['sometimes', 'boolean'],
        ];
    }
}
