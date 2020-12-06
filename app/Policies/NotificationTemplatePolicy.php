<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\NotificationTemplate;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NotificationTemplatePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the notification template.
     */
    public function view(User $user, NotificationTemplate $notificationTemplate): bool
    {
        return $user->can('send-notifications') || $user->can('manage-notification-templates');
    }

    /**
     * Determine whether the user can view any notification templates.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('send-notifications') || $user->can('manage-notification-templates');
    }

    /**
     * Determine whether the user can create notification templates.
     */
    public function create(User $user): bool
    {
        return $user->can('manage-notification-templates');
    }

    /**
     * Determine whether the user can update the notification template.
     */
    public function update(User $user, NotificationTemplate $notificationTemplate): bool
    {
        return $user->can('manage-notification-templates');
    }

    /**
     * Determine whether the user can delete the notification template.
     */
    public function delete(User $user, NotificationTemplate $notificationTemplate): bool
    {
        return $user->can('manage-notification-templates');
    }

    /**
     * Determine whether the user can restore the notification template.
     */
    public function restore(User $user, NotificationTemplate $notificationTemplate): bool
    {
        return $user->can('manage-notification-templates');
    }

    /**
     * Determine whether the user can permanently delete the notification template.
     */
    public function forceDelete(User $user, NotificationTemplate $notificationTemplate): bool
    {
        return false;
    }
}
