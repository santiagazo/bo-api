<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $authUser): bool
    {
        return $authUser->can('list:users');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $authUser, User $user): bool
    {
        // Users can always view themselves
        if ($authUser->id === $user->id) {
            return true;
        }

        return $authUser->can('view:users');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $authUser): bool
    {
        return $authUser->can('create:users');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $authUser, User $user): bool
    {
        // Users can always update themselves (profile)
        if ($authUser->id === $user->id) {
            return true;
        }

        return $authUser->can('edit:users');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $authUser, User $user): bool
    {
        // Users cannot delete themselves
        if ($authUser->id === $user->id) {
            return false;
        }

        return $authUser->can('delete:users');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $authUser, User $user): bool
    {
        return $authUser->can('restore:users');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $authUser, User $user): bool
    {
        return $authUser->can('destroy:users');
    }
}



