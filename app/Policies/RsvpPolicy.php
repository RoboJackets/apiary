<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Rsvp;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RsvpPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the rsvp.
     */
    public function view(User $user, Rsvp $rsvp): bool
    {
        return $user->can('read-rsvps');
    }

    /**
     * Determine whether the user can create rsvps.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the rsvp.
     */
    public function update(User $user, Rsvp $rsvp): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the rsvp.
     */
    public function delete(User $user, Rsvp $rsvp): bool
    {
        return $user->can('delete-rsvps');
    }

    /**
     * Determine whether the user can restore the rsvp.
     */
    public function restore(User $user, Rsvp $rsvp): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the rsvp.
     */
    public function forceDelete(User $user, Rsvp $rsvp): bool
    {
        return false;
    }

    public function replicate(User $user, Rsvp $rsvp): bool
    {
        return false;
    }
}
