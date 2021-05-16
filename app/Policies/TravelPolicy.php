<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Travel;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TravelPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Travel $travel): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('manage-travel');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Travel $travel): bool
    {
        return $user->can('manage-travel') || $travel->primaryContact === $user;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Travel $travel): bool
    {
        return $user->can('manage-travel');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Travel $travel): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Travel $travel): bool
    {
        return false;
    }
}
