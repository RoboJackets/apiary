<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter

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
     * @param \App\User  $user
     * @param \App\NotificationTemplate  $notificationTemplate
     *
     * @return bool
     */
    public function view(User $user, NotificationTemplate $notificationTemplate): bool
    {
        return $user->can('send-notifications') || $user->can('manage-notification-templates');
    }

    /**
     * Determine whether the user can view any notification templates.
     *
     * @param \App\User  $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('send-notifications') || $user->can('manage-notification-templates');
    }

    /**
     * Determine whether the user can create notification templates.
     *
     * @param \App\User  $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('manage-notification-templates');
    }

    /**
     * Determine whether the user can update the notification template.
     *
     * @param \App\User  $user
     * @param \App\NotificationTemplate  $notificationTemplate
     *
     * @return bool
     */
    public function update(User $user, NotificationTemplate $notificationTemplate): bool
    {
        return $user->can('manage-notification-templates');
    }

    /**
     * Determine whether the user can delete the notification template.
     *
     * @param \App\User  $user
     * @param \App\NotificationTemplate  $notificationTemplate
     *
     * @return bool
     */
    public function delete(User $user, NotificationTemplate $notificationTemplate): bool
    {
        return $user->can('manage-notification-templates');
    }

    /**
     * Determine whether the user can restore the notification template.
     *
     * @param \App\User  $user
     * @param \App\NotificationTemplate  $notificationTemplate
     *
     * @return bool
     */
    public function restore(User $user, NotificationTemplate $notificationTemplate): bool
    {
        return $user->can('manage-notification-templates');
    }

    /**
     * Determine whether the user can permanently delete the notification template.
     *
     * @param \App\User  $user
     * @param \App\NotificationTemplate  $notificationTemplate
     *
     * @return bool
     */
    public function forceDelete(User $user, NotificationTemplate $notificationTemplate): bool
    {
        return false;
    }
}
