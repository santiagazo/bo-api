<?php

namespace App\Models;

use App\Enums\MembershipScope;
use App\Enums\MembershipStatus;
use App\Support\CompanyContext;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'phone_country_code',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function assignedJobs(): HasMany
    {
        return $this->hasMany(Job::class, 'assigned_user_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function companyMemberships(): HasMany
    {
        return $this->memberships()
            ->where('scope_type', MembershipScope::Company)
            ->where('status', MembershipStatus::Active);
    }

    public function platformMemberships(): HasMany
    {
        return $this->memberships()
            ->where('scope_type', MembershipScope::Platform)
            ->where('status', MembershipStatus::Active);
    }

    public function scopeWithRelations($query)
    {
        return $query->with([
            'memberships.company.address',
            'memberships.roles.permissions',
        ]);
    }

    public function primaryCompanyMembership(): ?Membership
    {
        return $this->memberships()
            ->where('scope_type', MembershipScope::Company)
            ->where('status', MembershipStatus::Active)
            ->where('is_primary', true)
            ->first();
    }

    public function primaryCompanyId(): ?int
    {
        return $this->primaryCompanyMembership()?->company_id;
    }

    public function platformMembership(): ?Membership
    {
        return $this->memberships()
            ->where('scope_type', MembershipScope::Platform)
            ->where('status', MembershipStatus::Active)
            ->first();
    }

    public function activeMembership(?Company $company = null): ?Membership
    {
        $company ??= app(CompanyContext::class)->company();

        if ($company) {
            $membership = $this->memberships()
                ->where('scope_type', MembershipScope::Company)
                ->where('status', MembershipStatus::Active)
                ->where('company_id', $company->id)
                ->with('roles.permissions', 'company')
                ->first();

            if ($membership) {
                return $membership;
            }

            return $this->platformMembership()?->loadMissing('roles.permissions');
        }

        return $this->primaryCompanyMembership()?->loadMissing('roles.permissions', 'company')
            ?? $this->platformMembership()?->loadMissing('roles.permissions');
    }

    public function activeCompany(?Company $company = null): ?Company
    {
        $company ??= app(CompanyContext::class)->company();

        if ($company) {
            return $company;
        }

        return $this->primaryCompanyMembership()?->company;
    }

    public function currentCompanyId(?Company $company = null): ?int
    {
        return $this->activeCompany($company)?->id;
    }

    public function hasPlatformAccess(): bool
    {
        $membership = $this->platformMembership();

        if (! $membership) {
            return false;
        }

        return $membership->hasAnyPermission(['*:*', 'access:platform']);
    }

    public function hasPlatformPermission(string $permission): bool
    {
        $membership = $this->platformMembership();

        if (! $membership) {
            return false;
        }

        return $this->membershipHasPermission($membership, $permission);
    }

    public function hasSystemAccess(): bool
    {
        return $this->hasPlatformAccess();
    }

    public function hasGlobalAccess(): bool
    {
        return $this->hasPlatformAccess();
    }

    public function canImpersonateCompanies(): bool
    {
        $membership = $this->platformMembership();

        if (! $membership) {
            return false;
        }

        return $membership->hasAnyPermission(['*:*', 'impersonate:companies']);
    }

    public function canAccessCompany(Company|int|null $company): bool
    {
        if (! $company) {
            return $this->hasPlatformAccess() || $this->primaryCompanyId() !== null;
        }

        $companyId = $company instanceof Company ? $company->id : $company;

        if ($this->hasPlatformAccess()) {
            return true;
        }

        return $this->memberships()
            ->where('scope_type', MembershipScope::Company)
            ->where('status', MembershipStatus::Active)
            ->where('company_id', $companyId)
            ->exists();
    }

    public function permissionsForContext(?Company $company = null): Collection
    {
        $membership = $this->activeMembership($company);

        if (! $membership) {
            return collect();
        }

        return $membership->getAllPermissions()->pluck('name')->unique()->values();
    }

    public function rolesForContext(?Company $company = null): Collection
    {
        $membership = $this->activeMembership($company);

        if (! $membership) {
            return collect();
        }

        return $membership->roles;
    }

    public function hasPermissionInContext(string $permission, ?Company $company = null): bool
    {
        $membership = $this->activeMembership($company);

        if (! $membership) {
            return false;
        }

        return $this->membershipHasPermission($membership, $permission);
    }

    public function hasPermissionForCompany(string $permission, Company|int $company): bool
    {
        $companyModel = $company instanceof Company ? $company : Company::findOrFail($company);
        $activeCompany = app(CompanyContext::class)->company();

        if ($activeCompany && $activeCompany->id !== $companyModel->id) {
            return false;
        }

        if (! $this->canAccessCompany($companyModel)) {
            return false;
        }

        if (
            $this->memberships()
                ->where('scope_type', MembershipScope::Company)
                ->where('status', MembershipStatus::Active)
                ->where('company_id', $companyModel->id)
                ->exists()
        ) {
            return $this->hasPermissionInContext($permission, $companyModel);
        }

        return $this->hasPlatformPermission($permission);
    }

    public function permissionsForUiContext(?Company $company = null): Collection
    {
        $permissions = $this->permissionsForContext($company);

        if (! $company) {
            return $permissions;
        }

        $excludedPermissions = collect([
            'access:platform',
            'impersonate:companies',
            'create:companies',
            'list:companies',
            'delete:companies',
            'restore:companies',
            'destroy:companies',
        ]);

        $candidatePermissions = Permission::query()
            ->pluck('name')
            ->reject(fn (string $permission) => str_contains($permission, '*'))
            ->reject(fn (string $permission) => $excludedPermissions->contains($permission))
            ->values();

        $expandedPermissions = $permissions->flatMap(function (string $permission) use ($candidatePermissions, $excludedPermissions) {
            if ($excludedPermissions->contains($permission)) {
                return [];
            }

            if (! str_contains($permission, '*')) {
                return [$permission];
            }

            [$action, $subject] = array_pad(explode(':', $permission, 2), 2, '*');

            return $candidatePermissions->filter(function (string $candidate) use ($action, $subject) {
                [$candidateAction, $candidateSubject] = array_pad(explode(':', $candidate, 2), 2, null);

                return ($action === '*' || $action === $candidateAction)
                    && ($subject === '*' || $subject === $candidateSubject);
            })->values();
        });

        return $expandedPermissions
            ->unique()
            ->values();
    }

    protected function membershipHasPermission(Membership $membership, string $permission): bool
    {
        if ($membership->hasPermissionTo('*:*') || $membership->hasPermissionTo($permission)) {
            return true;
        }

        [$action, $subject] = array_pad(explode(':', $permission, 2), 2, '*');

        return $membership->hasPermissionTo("{$action}:*")
            || $membership->hasPermissionTo("*:{$subject}");
    }
}
