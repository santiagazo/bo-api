<?php

namespace App\Http\Resources;

use App\Support\CompanyContext;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $activeCompany = app(CompanyContext::class)->company();
        $companyMemberships = $this->whenLoaded('memberships', function () {
            return $this->memberships
                ->filter(fn ($membership) => $membership->company && $membership->isCompanyScope())
                ->map(fn ($membership) => [
                    'id' => $membership->id,
                    'scope_type' => $membership->scope_type?->value ?? $membership->scope_type,
                    'status' => $membership->status?->value ?? $membership->status,
                    'is_primary' => $membership->is_primary,
                    'company' => CompanyResource::make($membership->company),
                ])
                ->values();
        });

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'phone_country_code' => $this->phone_country_code,
            'platform_access' => $this->hasPlatformAccess(),
            'can_impersonate_companies' => $this->canImpersonateCompanies(),
            'primary_company_id' => $this->primaryCompanyId(),
            'current_company_id' => $this->currentCompanyId($activeCompany),
            'company' => CompanyResource::make($this->activeCompany($activeCompany)),
            'companies' => $companyMemberships,
            'permissions' => $this->permissionsForUiContext($activeCompany),
            'roles' => RoleResource::collection($this->rolesForContext($activeCompany)),
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
