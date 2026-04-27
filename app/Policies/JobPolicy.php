<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\Job;
use App\Models\User;
use App\Support\CompanyContext;

class JobPolicy
{
    public function viewAny(User $user): bool
    {
        $company = app(CompanyContext::class)->company();

        return $company !== null && $user->hasPermissionForCompany('list:jobs', $company);
    }

    public function view(User $user, Job $job): bool
    {
        $company = app(CompanyContext::class)->company();

        return $company !== null
            && $this->matchesCompany($job, $company)
            && $user->hasPermissionForCompany('view:jobs', $company);
    }

    public function create(User $user): bool
    {
        $company = app(CompanyContext::class)->company();

        return $company !== null && $user->hasPermissionForCompany('create:jobs', $company);
    }

    public function update(User $user, Job $job): bool
    {
        $company = app(CompanyContext::class)->company();

        return $company !== null
            && $this->matchesCompany($job, $company)
            && $user->hasPermissionForCompany('edit:jobs', $company);
    }

    public function delete(User $user, Job $job): bool
    {
        $company = app(CompanyContext::class)->company();

        return $company !== null
            && $this->matchesCompany($job, $company)
            && $user->hasPermissionForCompany('delete:jobs', $company);
    }

    protected function matchesCompany(Job $job, Company $company): bool
    {
        return $job->company_id === $company->id;
    }
}
