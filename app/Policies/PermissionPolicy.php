<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\Permission\Models\Permission;

class PermissionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the permission.
     */
    public function view(User $user, Permission $permission): bool
    {
        return $user->can('read-roles-and-permissions');
    }

    /**
     * Determine whether the user can view any users.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-roles-and-permissions');
    }

    /**
     * Determine whether the user can create permissions.
     */
    public function create(User $user): bool
    {
        return $user->can('write-roles-and-permissions');
    }

    /**
     * Determine whether the user can update the permission.
     */
    public function update(User $user, Permission $permission): bool
    {
        return $user->can('write-roles-and-permissions');
    }

    /**
     * Determine whether the user can delete the permission.
     */
    public function delete(User $user, Permission $permission): bool
    {
        return $user->can('write-roles-and-permissions');
    }

    /**
     * Determine whether the user can restore the permission.
     */
    public function restore(User $user, Permission $permission): bool
    {
        return $user->can('write-roles-and-permissions');
    }

    /**
     * Determine whether the user can permanently delete the permission.
     */
    public function forceDelete(User $user, Permission $permission): bool
    {
        return false;
    }

    public function replicate(User $user, Permission $permission): bool
    {
        return false;
    }
}
