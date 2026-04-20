<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

class PermissionPolicy
{
    /**
     * Determine whether the user can view any models.
     * Anyone who can list roles can also list permissions.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('list:roles');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Permission $permission): bool
    {
        return $user->can('view:roles');
    }

    /**
     * Permissions are managed via seeder, not via API.
     * Only system admins could potentially create permissions.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Permissions are managed via seeder, not via API.
     */
    public function update(User $user, Permission $permission): bool
    {
        return false;
    }

    /**
     * Permissions are managed via seeder, not via API.
     */
    public function delete(User $user, Permission $permission): bool
    {
        return false;
    }
}



