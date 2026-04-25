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

        return $user->can('view:organizations') && $user->canAccessOrganization($organization);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create:organizations') && $user->hasGlobalAccess();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Organization $organization): bool
    {
        return $user->can('edit:organizations') && $user->canAccessOrganization($organization);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Organization $organization): bool
    {
        // The global Built Ox org anchors global access and cannot be removed.
        if ($organization->isGlobal()) {
            return false;
        }

        return $user->can('delete:organizations') && $user->hasGlobalAccess();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Organization $organization): bool
    {
        return $user->can('restore:organizations') && $user->hasGlobalAccess();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Organization $organization): bool
    {
        // The global Built Ox org anchors global access and cannot be removed.
        if ($organization->isGlobal()) {
            return false;
        }

        return $user->can('destroy:organizations') && $user->hasGlobalAccess();
    }
}
