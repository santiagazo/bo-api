<?php

namespace App\Policies;

use App\Models\Location;
use App\Models\User;

class LocationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('list:locations');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Location $location): bool
    {
        return $user->can('view:locations') && $user->canAccessOrganization($location->organization_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create:locations');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Location $location): bool
    {
        return $user->can('edit:locations') && $user->canAccessOrganization($location->organization_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Location $location): bool
    {
        return $user->can('delete:locations') && $user->canAccessOrganization($location->organization_id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Location $location): bool
    {
        return $user->can('restore:locations') && $user->canAccessOrganization($location->organization_id);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Location $location): bool
    {
        return $user->can('destroy:locations') && $user->canAccessOrganization($location->organization_id);
    }
}
