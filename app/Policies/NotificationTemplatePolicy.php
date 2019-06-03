<?php declare(strict_types = 1);

namespace App\Policies;

use App\User;
use App\NotificationTemplate;
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
    public function view(User $user, NotificationTemplate $notificationTemplate): bool
    {
        return $user->can('send-notifications') || $user->can('manage-notification-templates');
    }

    /**
     * Determine whether the user can view any notification templates.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user): bool
    {
        return $user->can('send-notifications') || $user->can('manage-notification-templates');
    }

    /**
     * Determine whether the user can create notification templates.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user): bool
    {
        return $user->can('manage-notification-templates');
    }

    /**
     * Determine whether the user can update the notification template.
     *
     * @param  \App\User  $user
     * @param  \App\NotificationTemplate  $notificationTemplate
     * @return mixed
     */
    public function update(User $user, NotificationTemplate $notificationTemplate): bool
    {
        return $user->can('manage-notification-templates');
    }

    /**
     * Determine whether the user can delete the notification template.
     *
     * @param  \App\User  $user
     * @param  \App\NotificationTemplate  $notificationTemplate
     * @return mixed
     */
    public function delete(User $user, NotificationTemplate $notificationTemplate): bool
    {
        return $user->can('manage-notification-templates');
    }

    /**
     * Determine whether the user can restore the notification template.
     *
     * @param  \App\User  $user
     * @param  \App\NotificationTemplate  $notificationTemplate
     * @return mixed
     */
    public function restore(User $user, NotificationTemplate $notificationTemplate): bool
    {
        return $user->can('manage-notification-templates');
    }

    /**
     * Determine whether the user can permanently delete the notification template.
     *
     * @param  \App\User  $user
     * @param  \App\NotificationTemplate  $notificationTemplate
     * @return mixed
     */
    public function forceDelete(User $user, NotificationTemplate $notificationTemplate): bool
    {
        return false;
    }
}
