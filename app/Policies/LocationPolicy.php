<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\Location;
use App\Models\User;
use App\Support\CompanyContext;

class LocationPolicy
{
    public function viewAny(User $user): bool
    {
        $company = app(CompanyContext::class)->company();

        return $company !== null && $user->hasPermissionForCompany('list:locations', $company);
    }

    public function view(User $user, Location $location): bool
    {
        $company = app(CompanyContext::class)->company();

        return $company !== null
            && $this->matchesCompany($location, $company)
            && $user->canAccessLocation($location, $company)
            && $user->hasPermissionForCompany('view:locations', $company);
    }

    public function create(User $user): bool
    {
        $company = app(CompanyContext::class)->company();

        return $company !== null && $user->hasPermissionForCompany('create:locations', $company);
    }

    public function update(User $user, Location $location): bool
    {
        $company = app(CompanyContext::class)->company();

        return $company !== null
            && $this->matchesCompany($location, $company)
            && $user->canAccessLocation($location, $company)
            && $user->hasPermissionForCompany('edit:locations', $company);
    }

    public function delete(User $user, Location $location): bool
    {
        $company = app(CompanyContext::class)->company();

        return $company !== null
            && $this->matchesCompany($location, $company)
            && $user->canAccessLocation($location, $company)
            && $user->hasPermissionForCompany('delete:locations', $company);
    }

    protected function matchesCompany(Location $location, Company $company): bool
    {
        return $location->company_id === $company->id;
    }
}
