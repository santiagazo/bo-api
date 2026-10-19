<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCompanyBrandProfileRequest;
use App\Http\Resources\CompanyBrandProfileResource;
use App\Models\Company;
use App\Models\CompanyBrandProfile;

class CompanyBrandProfileController extends Controller
{
    public function show(Company $company): CompanyBrandProfileResource
    {
        $this->authorize('view', $company);

        return new CompanyBrandProfileResource(
            $company->brandProfile ?? new CompanyBrandProfile(['company_id' => $company->id])
        );
    }

    public function update(
        UpdateCompanyBrandProfileRequest $request,
        Company $company
    ): CompanyBrandProfileResource {
        $profile = $company->brandProfile()->withTrashed()->first()
            ?? new CompanyBrandProfile;

        $profile->fill($request->validated());
        $profile->company()->associate($company);
        $profile->restore();
        $profile->save();

        return new CompanyBrandProfileResource($profile->refresh());
    }
}
