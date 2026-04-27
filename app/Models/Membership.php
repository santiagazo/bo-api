<?php

namespace App\Models;

use App\Enums\MembershipScope;
use App\Enums\MembershipStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;

class Membership extends BaseModel
{
    use HasPermissions, HasRoles;

    protected string $guard_name = 'web';

    protected $fillable = [
        'user_id',
        'scope_type',
        'status',
        'company_id',
        'organization_id',
        'is_primary',
    ];

    protected $casts = [
        'scope_type' => MembershipScope::class,
        'status' => MembershipStatus::class,
        'is_primary' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function isPlatformScope(): bool
    {
        return $this->scope_type === MembershipScope::Platform;
    }

    public function isCompanyScope(): bool
    {
        return $this->scope_type === MembershipScope::Company;
    }

    public function isOrganizationScope(): bool
    {
        return $this->scope_type === MembershipScope::Organization;
    }
}
