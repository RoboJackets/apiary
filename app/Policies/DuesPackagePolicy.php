<?php declare(strict_types = 1);

namespace App\Policies;

use App\User;
use App\DuesPackage;
use Illuminate\Auth\Access\HandlesAuthorization;

class DuesPackagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the dues package.
     *
     * @param  \App\User  $user
     * @param  \App\DuesPackage  $duesPackage
     * @return mixed
     */
    public function view(User $user, DuesPackage $duesPackage): bool
    {
        // Normal users have this, but Nova in general is limited by access-nova
        return $user->can('read-dues-packages');
    }

    /**
     * Determine whether the user can view any dues packages.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-dues-packages');
    }

    /**
     * Determine whether the user can create dues packages.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user): bool
    {
        return $user->can('create-dues-packages');
    }

    /**
     * Determine whether the user can update the dues package.
     *
     * @param  \App\User  $user
     * @param  \App\DuesPackage  $duesPackage
     * @return mixed
     */
    public function update(User $user, DuesPackage $duesPackage): bool
    {
        return $user->can('update-dues-packages');
    }

    /**
     * Determine whether the user can delete the dues package.
     *
     * @param  \App\User  $user
     * @param  \App\DuesPackage  $duesPackage
     * @return mixed
     */
    public function delete(User $user, DuesPackage $duesPackage): bool
    {
        return $user->can('delete-dues-packages');
    }

    /**
     * Determine whether the user can restore the dues package.
     *
     * @param  \App\User  $user
     * @param  \App\DuesPackage  $duesPackage
     * @return mixed
     */
    public function restore(User $user, DuesPackage $duesPackage): bool
    {
        return $user->can('create-dues-packages');
    }

    /**
     * Determine whether the user can permanently delete the dues package.
     *
     * @param  \App\User  $user
     * @param  \App\DuesPackage  $duesPackage
     * @return mixed
     */
    public function forceDelete(User $user, DuesPackage $duesPackage): bool
    {
        return false;
    }
}
