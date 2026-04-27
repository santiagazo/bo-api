<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;
use App\Support\CompanyContext;

class CompanyPolicy
{
    public function viewAny(User $user): bool
    {
        $activeCompany = app(CompanyContext::class)->company();

        if ($activeCompany) {
            return $user->hasPermissionForCompany('view:companies', $activeCompany);
        }

        return $user->hasPlatformPermission('list:companies');
    }

    public function view(User $user, Company $company): bool
    {
        $activeCompany = app(CompanyContext::class)->company();

        if ($activeCompany && $activeCompany->id !== $company->id) {
            return false;
        }

        return $user->hasPermissionForCompany('view:companies', $company);
    }

    public function create(User $user): bool
    {
        if (app(CompanyContext::class)->company()) {
            return false;
        }

        return $user->hasPlatformPermission('create:companies');
    }

    public function update(User $user, Company $company): bool
    {
        $activeCompany = app(CompanyContext::class)->company();

        if ($activeCompany && $activeCompany->id !== $company->id) {
            return false;
        }

        return $user->hasPermissionForCompany('edit:companies', $company);
    }

    public function delete(User $user, Company $company): bool
    {
        if (app(CompanyContext::class)->company()) {
            return false;
        }

        return $user->hasPlatformPermission('delete:companies');
    }

    public function restore(User $user, Company $company): bool
    {
        if (app(CompanyContext::class)->company()) {
            return false;
        }

        return $user->hasPlatformPermission('restore:companies');
    }

    public function forceDelete(User $user, Company $company): bool
    {
        if (app(CompanyContext::class)->company()) {
            return false;
        }

        return $user->hasPlatformPermission('destroy:companies');
    }
}
