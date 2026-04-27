<?php

namespace App\Policies;

use App\Enums\MembershipScope;
use App\Enums\MembershipStatus;
use App\Models\Company;
use App\Models\User;
use App\Support\CompanyContext;

class UserPolicy
{
    public function viewAny(User $authUser): bool
    {
        $company = app(CompanyContext::class)->company();

        return $company !== null && $authUser->hasPermissionForCompany('list:users', $company);
    }

    public function view(User $authUser, User $user): bool
    {
        if ($authUser->id === $user->id) {
            return true;
        }

        $company = app(CompanyContext::class)->company();

        return $company !== null
            && $this->belongsToCompany($user, $company)
            && $authUser->hasPermissionForCompany('view:users', $company);
    }

    public function create(User $authUser): bool
    {
        $company = app(CompanyContext::class)->company();

        return $company !== null && $authUser->hasPermissionForCompany('create:users', $company);
    }

    public function update(User $authUser, User $user): bool
    {
        if ($authUser->id === $user->id) {
            return true;
        }

        $company = app(CompanyContext::class)->company();

        return $company !== null
            && $this->belongsToCompany($user, $company)
            && $authUser->hasPermissionForCompany('edit:users', $company);
    }

    public function delete(User $authUser, User $user): bool
    {
        if ($authUser->id === $user->id) {
            return false;
        }

        $company = app(CompanyContext::class)->company();

        return $company !== null
            && $this->belongsToCompany($user, $company)
            && $authUser->hasPermissionForCompany('delete:users', $company);
    }

    protected function belongsToCompany(User $user, Company $company): bool
    {
        return $user->memberships()
            ->where('scope_type', MembershipScope::Company)
            ->where('status', MembershipStatus::Active)
            ->where('company_id', $company->id)
            ->exists();
    }
}
