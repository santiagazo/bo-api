<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserGroup;

class UserGroupPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('list:user-groups');
    }

    public function view(User $user, UserGroup $userGroup): bool
    {
        if (! $user->can('view:user-groups')) {
            return false;
        }

        return $user->hasGlobalAccess() || $user->organization_id === $userGroup->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('create:user-groups');
    }

    public function update(User $user, UserGroup $userGroup): bool
    {
        if (! $user->can('edit:user-groups')) {
            return false;
        }

        return $user->hasGlobalAccess() || $user->organization_id === $userGroup->organization_id;
    }

    public function delete(User $user, UserGroup $userGroup): bool
    {
        if (! $user->can('delete:user-groups')) {
            return false;
        }

        return $user->hasGlobalAccess() || $user->organization_id === $userGroup->organization_id;
    }

    public function restore(User $user, UserGroup $userGroup): bool
    {
        if (! $user->can('restore:user-groups')) {
            return false;
        }

        return $user->hasGlobalAccess() || $user->organization_id === $userGroup->organization_id;
    }

    public function forceDelete(User $user, UserGroup $userGroup): bool
    {
        if (! $user->can('destroy:user-groups')) {
            return false;
        }

        return $user->hasGlobalAccess() || $user->organization_id === $userGroup->organization_id;
    }
}
