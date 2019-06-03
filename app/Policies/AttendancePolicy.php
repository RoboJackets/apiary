<?php declare(strict_types = 1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter

namespace App\Policies;

use App\User;
use App\Attendance;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttendancePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the attendance.
     *
     * @param \App\User  $user
     * @param \App\Attendance  $attendance
     *
     * @return bool
     */
    public function view(User $user, Attendance $attendance): bool
    {
        return $user->can('read-attendance');
    }

    /**
     * Determine whether the user can view any attendance.
     *
     * @param \App\User  $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-attendance');
    }

    /**
     * Determine whether the user can create attendances.
     *
     * @param \App\User  $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        // Attendance should be created in the old admin interface for now.
        return false;
    }

    /**
     * Determine whether the user can update the attendance.
     *
     * @param \App\User  $user
     * @param \App\Attendance  $attendance
     *
     * @return bool
     */
    public function update(User $user, Attendance $attendance): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the attendance.
     *
     * @param \App\User  $user
     * @param \App\Attendance  $attendance
     *
     * @return bool
     */
    public function delete(User $user, Attendance $attendance): bool
    {
        return $user->can('delete-attendance');
    }

    /**
     * Determine whether the user can restore the attendance.
     *
     * @param \App\User  $user
     * @param \App\Attendance  $attendance
     *
     * @return bool
     */
    public function restore(User $user, Attendance $attendance): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the attendance.
     *
     * @param \App\User  $user
     * @param \App\Attendance  $attendance
     *
     * @return bool
     */
    public function forceDelete(User $user, Attendance $attendance): bool
    {
        return false;
    }
}
