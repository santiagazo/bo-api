<?php

namespace App\Http\Requests;

use App\Enums\JobStatus;
use App\Enums\JobType;
use App\Enums\LossType;
use App\Enums\PropertyType;
use App\Models\Job;
use App\Support\CompanyContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Job::class);
    }

    public function rules(): array
    {
        $companyId = app(CompanyContext::class)->companyId();

        return [
            'customer_id' => [
                'required',
                'integer',
                Rule::exists('customers', 'id')->where('company_id', $companyId),
            ],
            'location_id' => [
                'required',
                'integer',
                Rule::exists('locations', 'id')->where('company_id', $companyId),
            ],
            'reference_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in(array_map(fn ($case) => $case->value, JobStatus::cases()))],
            'type' => ['required', 'string', Rule::in(array_map(fn ($case) => $case->value, JobType::cases()))],
            'property_type' => ['required', 'string', Rule::in(array_map(fn ($case) => $case->value, PropertyType::cases()))],
            'loss_type' => ['required', 'string', Rule::in(array_map(fn ($case) => $case->value, LossType::cases()))],
            'claim_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'policy_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'opened_at' => ['sometimes', 'nullable', 'date'],
            'date_of_loss' => ['sometimes', 'nullable', 'date'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'property_address' => ['required', 'array'],
            'property_address.name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_address.line1' => ['required', 'string', 'max:255'],
            'property_address.line2' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_address.city' => ['required', 'string', 'max:255'],
            'property_address.state' => ['required', 'string', 'max:255'],
            'property_address.postal_code' => ['required', 'string', 'max:255'],
            'property_address.country' => ['required', 'string', 'max:255'],
        ];
    }
}
