<?php

namespace App\Models;

use App\Enums\RoleType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'guard_name',
        'organization_id',
        'type',
        'display_name',
    ];

    protected $casts = [
        'type' => RoleType::class,
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Override the users relationship to use our User model explicitly.
     */
    public function users(): MorphToMany
    {
        return $this->morphedByMany(
            User::class,
            'model',
            config('permission.table_names.model_has_roles'),
            config('permission.column_names.role_pivot_key') ?: 'role_id',
            config('permission.column_names.model_morph_key') ?: 'model_id'
        );
    }
}
