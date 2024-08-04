<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RemoteAttendanceLink;
use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RemoteAttendanceLinkPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the remote attendance link.
     */
    public function view(User $user, RemoteAttendanceLink $link): bool
    {
        if (! $user->can('read-remote-attendance-links')) {
            return false;
        }

        if (is_a($link->attendable, Team::class) && ! $link->attendable->visible) {
            return $user->can('read-teams-hidden');
        }

        return true;
    }

    /**
     * Determine whether the user can view any remote attendance links.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-remote-attendance-links');
    }

    /**
     * Determine whether the user can create remote attendance links.
     */
    public function create(User $user): bool
    {
        // Should mostly be created with the Nova action
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the remote attendance link.
     */
    public function update(User $user, RemoteAttendanceLink $link): bool
    {
        return $user->can('update-remote-attendance-links');
    }

    /**
     * Determine whether the user can delete the remote attendance link.
     */
    public function delete(User $user, RemoteAttendanceLink $link): bool
    {
        return $user->can('delete-remote-attendance-links');
    }

    /**
     * Determine whether the user can restore the remote attendance link.
     */
    public function restore(User $user, RemoteAttendanceLink $link): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the remote attendance link.
     */
    public function forceDelete(User $user, RemoteAttendanceLink $link): bool
    {
        return false;
    }

    public function replicate(User $user, RemoteAttendanceLink $link): bool
    {
        return false;
    }
}
