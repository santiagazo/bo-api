<?php

namespace App\Models;

use App\Enums\RoleScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'guard_name',
        'company_id',
        'scope_type',
        'display_name',
    ];

    protected $casts = [
        'scope_type' => RoleScope::class,
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function memberships(): MorphToMany
    {
        return $this->morphedByMany(
            Membership::class,
            'model',
            config('permission.table_names.model_has_roles'),
            config('permission.column_names.role_pivot_key') ?: 'role_id',
            config('permission.column_names.model_morph_key') ?: 'model_id'
        );
    }
}
