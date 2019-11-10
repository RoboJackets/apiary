<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter

namespace App\Policies;

use App\User;
use Spatie\Permission\Models\Permission;
use Illuminate\Auth\Access\HandlesAuthorization;

class PermissionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the permission.
     *
     * @param \App\User  $user
     * @param \Spatie\Permission\Models\Permission  $permission
     *
     * @return bool
     */
    public function view(User $user, Permission $permission): bool
    {
        return $user->can('read-roles-and-permissions');
    }

    /**
     * Determine whether the user can view any users.
     *
     * @param \App\User  $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-roles-and-permissions');
    }

    /**
     * Determine whether the user can create permissions.
     *
     * @param \App\User  $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('write-roles-and-permissions');
    }

    /**
     * Determine whether the user can update the permission.
     *
     * @param \App\User  $user
     * @param \Spatie\Permission\Models\Permission  $permission
     *
     * @return bool
     */
    public function update(User $user, Permission $permission): bool
    {
        return $user->can('write-roles-and-permissions');
    }

    /**
     * Determine whether the user can delete the permission.
     *
     * @param \App\User  $user
     * @param \Spatie\Permission\Models\Permission  $permission
     *
     * @return bool
     */
    public function delete(User $user, Permission $permission): bool
    {
        return $user->can('write-roles-and-permissions');
    }

    /**
     * Determine whether the user can restore the permission.
     *
     * @param \App\User  $user
     * @param \Spatie\Permission\Models\Permission  $permission
     *
     * @return bool
     */
    public function restore(User $user, Permission $permission): bool
    {
        return $user->can('write-roles-and-permissions');
    }

    /**
     * Determine whether the user can permanently delete the permission.
     *
     * @param \App\User  $user
     * @param \Spatie\Permission\Models\Permission  $permission
     *
     * @return bool
     */
    public function forceDelete(User $user, Permission $permission): bool
    {
        return false;
    }
}
