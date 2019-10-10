<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter

namespace App\Policies;

use App\User;
use App\Event;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the event.
     *
     * @param \App\User  $user
     * @param \App\Event  $event
     *
     * @return bool
     */
    public function view(User $user, Event $event): bool
    {
        // Normal users have this, but Nova in general is limited by access-nova
        return $user->can('read-events');
    }

    /**
     * Determine whether the user can view any events.
     *
     * @param \App\User  $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-events');
    }

    /**
     * Determine whether the user can create events.
     *
     * @param \App\User  $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create-events');
    }

    /**
     * Determine whether the user can update the event.
     *
     * @param \App\User  $user
     * @param \App\Event  $event
     *
     * @return bool
     */
    public function update(User $user, Event $event): bool
    {
        return $user->can('update-events');
    }

    /**
     * Determine whether the user can delete the event.
     *
     * @param \App\User  $user
     * @param \App\Event  $event
     *
     * @return bool
     */
    public function delete(User $user, Event $event): bool
    {
        return $user->can('delete-events');
    }

    /**
     * Determine whether the user can restore the event.
     *
     * @param \App\User  $user
     * @param \App\Event  $event
     *
     * @return bool
     */
    public function restore(User $user, Event $event): bool
    {
        return $user->can('create-events');
    }

    /**
     * Determine whether the user can permanently delete the event.
     *
     * @param \App\User  $user
     * @param \App\Event  $event
     *
     * @return bool
     */
    public function forceDelete(User $user, Event $event): bool
    {
        return false;
    }
}
