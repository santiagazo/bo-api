<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\Customer;
use App\Models\User;
use App\Support\CompanyContext;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        $company = app(CompanyContext::class)->company();

        return $company !== null && $user->hasPermissionForCompany('list:customers', $company);
    }

    public function view(User $user, Customer $customer): bool
    {
        $company = app(CompanyContext::class)->company();

        return $company !== null
            && $this->matchesCompany($customer, $company)
            && $user->hasPermissionForCompany('view:customers', $company);
    }

    public function create(User $user): bool
    {
        $company = app(CompanyContext::class)->company();

        return $company !== null && $user->hasPermissionForCompany('create:customers', $company);
    }

    public function update(User $user, Customer $customer): bool
    {
        $company = app(CompanyContext::class)->company();

        return $company !== null
            && $this->matchesCompany($customer, $company)
            && $user->hasPermissionForCompany('edit:customers', $company);
    }

    public function delete(User $user, Customer $customer): bool
    {
        $company = app(CompanyContext::class)->company();

        return $company !== null
            && $this->matchesCompany($customer, $company)
            && $user->hasPermissionForCompany('delete:customers', $company);
    }

    protected function matchesCompany(Customer $customer, Company $company): bool
    {
        return $customer->company_id === $company->id;
    }
}
