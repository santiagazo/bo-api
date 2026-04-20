<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('list:organizations');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Organization $organization): bool
    {
        // Users can view their own organization
        if ($user->organization_id === $organization->id) {
            return true;
        }

        return $user->can('view:organizations');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create:organizations');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Organization $organization): bool
    {
        return $user->can('edit:organizations');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Organization $organization): bool
    {
        // Prevent deleting Built Ox org (ID 1)
        if ($organization->id === 1) {
            return false;
        }

        return $user->can('delete:organizations');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Organization $organization): bool
    {
        return $user->can('restore:organizations');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Organization $organization): bool
    {
        // Prevent force deleting Built Ox org
        if ($organization->id === 1) {
            return false;
        }

        return $user->can('destroy:organizations');
    }
}



