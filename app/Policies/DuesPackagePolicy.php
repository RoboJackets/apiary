<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DuesPackage;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DuesPackagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the dues package.
     */
    public function view(User $user, DuesPackage $package): bool
    {
        // Normal users have this, but Nova in general is limited by access-nova
        return $user->can('read-dues-packages');
    }

    /**
     * Determine whether the user can view any dues packages.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-dues-packages');
    }

    /**
     * Determine whether the user can create dues packages.
     */
    public function create(User $user): bool
    {
        return $user->can('create-dues-packages-manually');
    }

    /**
     * Determine whether the user can update the dues package.
     */
    public function update(User $user, DuesPackage $package): bool
    {
        return $user->can('update-dues-packages');
    }

    /**
     * Determine whether the user can delete the dues package.
     */
    public function delete(User $user, DuesPackage $package): bool
    {
        return $user->can('delete-dues-packages');
    }

    /**
     * Determine whether the user can restore the dues package.
     */
    public function restore(User $user, DuesPackage $package): bool
    {
        return $user->can('create-dues-packages');
    }

    /**
     * Determine whether the user can permanently delete the dues package.
     */
    public function forceDelete(User $user, DuesPackage $package): bool
    {
        return false;
    }

    public function attachMerchandise(User $user, DuesPackage $package): bool
    {
        return $user->can('create-merchandise');
    }

    public function attachAnyMerchandise(User $user, DuesPackage $package): bool
    {
        return $user->can('create-merchandise');
    }

    public function detachMerchandise(User $user, DuesPackage $package): bool
    {
        return $user->can('create-merchandise');
    }

    public function replicate(User $user, DuesPackage $package): bool
    {
        return false;
    }
}
