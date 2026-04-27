<?php

namespace App\Support;

use App\Models\Company;

class CompanyContext
{
    protected ?Company $company = null;

    public function setCompany(Company $company): void
    {
        $this->company = $company;
    }

    public function company(): ?Company
    {
        return $this->company;
    }

    public function companyId(): ?int
    {
        return $this->company?->id;
    }
}
