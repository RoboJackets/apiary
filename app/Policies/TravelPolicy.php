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
    public function viewAny(User $user): true
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Travel $travel): true
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
        // admins can always update trips
        return $user->hasRole('admin') ||
            (
                // if the trip is in draft status...
                $travel->status === 'draft' &&
                (
                    // then users with the manage-travel permission can also update the trip
                    $user->can('manage-travel') ||
                    // or the primary contact can update the trip
                    $travel->primary_contact_user_id === $user->id
                )
            );
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Travel $travel): bool
    {
        // admins can always delete trips
        return $user->hasRole('admin') ||
            (
                // if the trip is not complete...
                $travel->status !== 'complete' &&
                (
                    // then users with the manage-travel permission can also delete the trip
                    $user->can('manage-travel') ||
                    // or the primary contact can delete the trip
                    $travel->primary_contact_user_id === $user->id
                )
            );
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Travel $travel): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Travel $travel): false
    {
        return false;
    }

    public function replicate(User $user, Travel $travel): false
    {
        return false;
    }

    public function addTravelAssignment(User $user, Travel $travel): bool
    {
        return $this->update($user, $travel);
    }
}
