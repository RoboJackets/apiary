<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the event.
     */
    public function view(User $user, Event $event): bool
    {
        // Normal users have this, but Nova in general is limited by access-nova
        return $user->can('read-events');
    }

    /**
     * Determine whether the user can view any events.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-events');
    }

    /**
     * Determine whether the user can create events.
     */
    public function create(User $user): bool
    {
        return $user->can('create-events');
    }

    /**
     * Determine whether the user can update the event.
     */
    public function update(User $user, Event $event): bool
    {
        return $user->can('update-events');
    }

    /**
     * Determine whether the user can delete the event.
     */
    public function delete(User $user, Event $event): bool
    {
        return $user->can('delete-events');
    }

    /**
     * Determine whether the user can restore the event.
     */
    public function restore(User $user, Event $event): bool
    {
        return $user->can('create-events');
    }

    /**
     * Determine whether the user can permanently delete the event.
     */
    public function forceDelete(User $user, Event $event): bool
    {
        return false;
    }

    public function replicate(User $user, Event $event): bool
    {
        return false;
    }
}
