<?php

namespace App\Http\Requests;

use App\Enums\LocationColor;
use App\Models\Location;
use App\Rules\ValidPhoneNumber;
use App\Support\CompanyContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Location::class);
    }

    public function rules(): array
    {
        $companyId = app(CompanyContext::class)->companyId();

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
                Rule::unique('locations', 'code')
                    ->where(fn ($query) => $query->where('company_id', $companyId))
                    ->withoutTrashed(),
            ],
            'sort_order' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'color' => ['sometimes', 'nullable', Rule::enum(LocationColor::class)],
            'status' => ['required', 'string', 'max:50'],
            'timezone' => ['sometimes', 'nullable', 'string', 'max:100'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20', new ValidPhoneNumber],
            'phone_country_code' => ['sometimes', 'nullable', 'string', 'in:US,CA'],
            'address_id' => ['sometimes', 'nullable', 'integer', 'exists:addresses,id'],
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
