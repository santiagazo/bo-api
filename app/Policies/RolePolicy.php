<?php

namespace App\Policies;

use App\Enums\RoleType;
use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('list:roles');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Role $role): bool
    {
        return $user->can('view:roles') && $this->withinTenant($user, $role);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create:roles');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Role $role): bool
    {
        return $user->can('edit:roles') && $this->canModifyWithinTenant($user, $role);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Role $role): bool
    {
        return $user->can('delete:roles') && $this->canModifyWithinTenant($user, $role);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Role $role): bool
    {
        return $user->can('restore:roles') && $this->canModifyWithinTenant($user, $role);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Role $role): bool
    {
        return $user->can('destroy:roles') && $this->canModifyWithinTenant($user, $role);
    }

    protected function withinTenant(User $user, Role $role): bool
    {
        if ($role->organization_id === null) {
            return $user->hasSystemAccess() || $role->type === RoleType::Organization;
        }

        return $user->canAccessOrganization($role->organization_id);
    }

    protected function canModifyWithinTenant(User $user, Role $role): bool
    {
        if ($role->organization_id === null) {
            return $user->hasSystemAccess();
        }

        return $user->canAccessOrganization($role->organization_id);
    }
}
