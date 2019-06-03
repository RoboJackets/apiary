<?php declare(strict_types = 1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter

namespace App\Policies;

use App\Rsvp;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RsvpPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the rsvp.
     *
     * @param \App\User  $user
     * @param \App\Rsvp  $rsvp
     *
     * @return bool
     */
    public function view(User $user, Rsvp $rsvp): bool
    {
        return $user->can('read-rsvps');
    }

    /**
     * Determine whether the user can create rsvps.
     *
     * @param \App\User  $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the rsvp.
     *
     * @param \App\User  $user
     * @param \App\Rsvp  $rsvp
     *
     * @return bool
     */
    public function update(User $user, Rsvp $rsvp): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the rsvp.
     *
     * @param \App\User  $user
     * @param \App\Rsvp  $rsvp
     *
     * @return bool
     */
    public function delete(User $user, Rsvp $rsvp): bool
    {
        return $user->can('delete-rsvps');
    }

    /**
     * Determine whether the user can restore the rsvp.
     *
     * @param \App\User  $user
     * @param \App\Rsvp  $rsvp
     *
     * @return bool
     */
    public function restore(User $user, Rsvp $rsvp): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the rsvp.
     *
     * @param \App\User  $user
     * @param \App\Rsvp  $rsvp
     *
     * @return bool
     */
    public function forceDelete(User $user, Rsvp $rsvp): bool
    {
        return false;
    }
}
