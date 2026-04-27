<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use App\Support\CompanyContext;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        $company = app(CompanyContext::class)->company();

        return $company !== null && $user->hasPermissionForCompany('list:roles', $company);
    }

    public function view(User $user, Role $role): bool
    {
        $company = app(CompanyContext::class)->company();

        return $company !== null
            && $this->belongsToCompanyContext($role, $company->id)
            && $user->hasPermissionForCompany('view:roles', $company);
    }

    public function create(User $user): bool
    {
        $company = app(CompanyContext::class)->company();

        return $company !== null && $user->hasPermissionForCompany('create:roles', $company);
    }

    public function update(User $user, Role $role): bool
    {
        $company = app(CompanyContext::class)->company();

        return $company !== null
            && $this->belongsToCompanyContext($role, $company->id)
            && $user->hasPermissionForCompany('edit:roles', $company);
    }

    public function delete(User $user, Role $role): bool
    {
        $company = app(CompanyContext::class)->company();

        return $company !== null
            && $this->belongsToCompanyContext($role, $company->id)
            && $user->hasPermissionForCompany('delete:roles', $company);
    }

    protected function belongsToCompanyContext(Role $role, int $companyId): bool
    {
        return $role->company_id === null || $role->company_id === $companyId;
    }
}
