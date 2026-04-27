<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;
use App\Support\CompanyContext;

class OrganizationPolicy
{
    public function viewAny(User $user): bool
    {
        $company = app(CompanyContext::class)->company();

        return $company !== null && $user->hasPermissionForCompany('list:organizations', $company);
    }

    public function view(User $user, Organization $organization): bool
    {
        $company = app(CompanyContext::class)->company();

        return $company !== null
            && $organization->company_id === $company->id
            && $user->hasPermissionForCompany('view:organizations', $company);
    }

    public function create(User $user): bool
    {
        $company = app(CompanyContext::class)->company();

        return $company !== null && $user->hasPermissionForCompany('create:organizations', $company);
    }

    public function update(User $user, Organization $organization): bool
    {
        $company = app(CompanyContext::class)->company();

        return $company !== null
            && $organization->company_id === $company->id
            && $user->hasPermissionForCompany('edit:organizations', $company);
    }

    public function delete(User $user, Organization $organization): bool
    {
        $company = app(CompanyContext::class)->company();

        return $company !== null
            && $organization->company_id === $company->id
            && $user->hasPermissionForCompany('delete:organizations', $company);
    }
}
