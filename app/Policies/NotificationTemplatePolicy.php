<?php

namespace App\Policies;

use App\NotificationTemplate;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NotificationTemplatePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the notification template.
     *
     * @param  \App\User  $user
     * @param  \App\NotificationTemplate  $notificationTemplate
     * @return mixed
     */
    public function view(User $user, NotificationTemplate $notificationTemplate)
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view any notification templates.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can create notification templates.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the notification template.
     *
     * @param  \App\User  $user
     * @param  \App\NotificationTemplate  $notificationTemplate
     * @return mixed
     */
    public function update(User $user, NotificationTemplate $notificationTemplate)
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the notification template.
     *
     * @param  \App\User  $user
     * @param  \App\NotificationTemplate  $notificationTemplate
     * @return mixed
     */
    public function delete(User $user, NotificationTemplate $notificationTemplate)
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the notification template.
     *
     * @param  \App\User  $user
     * @param  \App\NotificationTemplate  $notificationTemplate
     * @return mixed
     */
    public function restore(User $user, NotificationTemplate $notificationTemplate)
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the notification template.
     *
     * @param  \App\User  $user
     * @param  \App\NotificationTemplate  $notificationTemplate
     * @return mixed
     */
    public function forceDelete(User $user, NotificationTemplate $notificationTemplate)
    {
        return false;
    }
}
