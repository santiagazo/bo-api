<?php

namespace App\Http\Requests;

use App\Enums\JobStatus;
use App\Enums\JobType;
use App\Enums\LossType;
use App\Enums\PropertyType;
use App\Support\CompanyContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('job'));
    }

    public function rules(): array
    {
        $companyId = app(CompanyContext::class)->companyId();

        return [
            'customer_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('customers', 'id')->where('company_id', $companyId),
            ],
            'location_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('locations', 'id')->where('company_id', $companyId),
            ],
            'reference_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'status' => ['sometimes', 'required', 'string', Rule::in(array_map(fn ($case) => $case->value, JobStatus::cases()))],
            'type' => ['sometimes', 'required', 'string', Rule::in(array_map(fn ($case) => $case->value, JobType::cases()))],
            'property_type' => ['sometimes', 'required', 'string', Rule::in(array_map(fn ($case) => $case->value, PropertyType::cases()))],
            'loss_type' => ['sometimes', 'required', 'string', Rule::in(array_map(fn ($case) => $case->value, LossType::cases()))],
            'claim_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'policy_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'opened_at' => ['sometimes', 'nullable', 'date'],
            'date_of_loss' => ['sometimes', 'nullable', 'date'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'property_address' => ['sometimes', 'array'],
            'property_address.name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_address.line1' => ['required_with:property_address', 'string', 'max:255'],
            'property_address.line2' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_address.city' => ['required_with:property_address', 'string', 'max:255'],
            'property_address.state' => ['required_with:property_address', 'string', 'max:255'],
            'property_address.postal_code' => ['required_with:property_address', 'string', 'max:255'],
            'property_address.country' => ['required_with:property_address', 'string', 'max:255'],
        ];
    }
}
