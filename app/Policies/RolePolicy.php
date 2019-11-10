<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the role.
     *
     * @param \App\User  $user
     * @param \Spatie\Permission\Models\Role  $role
     *
     * @return bool
     */
    public function view(User $user, Role $role): bool
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
     * Determine whether the user can create roles.
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
     * Determine whether the user can update the role.
     *
     * @param \App\User  $user
     * @param \Spatie\Permission\Models\Role  $role
     *
     * @return bool
     */
    public function update(User $user, Role $role): bool
    {
        return $user->can('write-roles-and-permissions');
    }

    /**
     * Determine whether the user can delete the role.
     *
     * @param \App\User  $user
     * @param \Spatie\Permission\Models\Role  $role
     *
     * @return bool
     */
    public function delete(User $user, Role $role): bool
    {
        return $user->can('write-roles-and-permissions');
    }

    /**
     * Determine whether the user can restore the role.
     *
     * @param \App\User  $user
     * @param \Spatie\Permission\Models\Role  $role
     *
     * @return bool
     */
    public function restore(User $user, Role $role): bool
    {
        return $user->can('write-roles-and-permissions');
    }

    /**
     * Determine whether the user can permanently delete the role.
     *
     * @param \App\User  $user
     * @param \Spatie\Permission\Models\Role  $role
     *
     * @return bool
     */
    public function forceDelete(User $user, Role $role): bool
    {
        return false;
    }
}
