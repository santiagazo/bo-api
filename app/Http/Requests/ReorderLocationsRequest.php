<?php

namespace App\Http\Requests;

use App\Support\CompanyContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReorderLocationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = app(CompanyContext::class)->companyId();

        return [
            'location_ids' => ['required', 'array', 'min:1'],
            'location_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('locations', 'id')
                    ->where(fn ($query) => $query->where('company_id', $companyId))
                    ->withoutTrashed(),
            ],
        ];
    }
}
