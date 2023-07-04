<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Merchandise;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MerchandisePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-merchandise');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Merchandise $merchandise): bool
    {
        return $user->can('read-merchandise');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-merchandise');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Merchandise $merchandise): bool
    {
        return $user->can('update-merchandise');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Merchandise $merchandise): bool
    {
        return $user->can('delete-merchandise');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Merchandise $merchandise): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Merchandise $merchandise): bool
    {
        return false;
    }

    public function attachDuesTransaction(User $user, Merchandise $merchandise): bool
    {
        return $user->hasRole('admin');
    }

    public function attachAnyDuesTransaction(User $user, Merchandise $merchandise): bool
    {
        return $user->hasRole('admin');
    }

    public function detachDuesTransaction(User $user, Merchandise $merchandise): bool
    {
        return $user->hasRole('admin');
    }

    public function attachDuesPackage(User $user, Merchandise $merchandise): bool
    {
        return $user->can('create-merchandise');
    }

    public function attachAnyDuesPackage(User $user, Merchandise $merchandise): bool
    {
        return $user->can('create-merchandise');
    }

    public function detachDuesPackage(User $user, Merchandise $merchandise): bool
    {
        return $user->can('create-merchandise');
    }

    public function replicate(User $user, Merchandise $merchandise): bool
    {
        return false;
    }
}
