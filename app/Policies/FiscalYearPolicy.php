<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\FiscalYear;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FiscalYearPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @psalm-pure
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @psalm-pure
     */
    public function view(User $user, FiscalYear $fiscalYear): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-fiscal-years');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, FiscalYear $fiscalYear): bool
    {
        return $user->can('update-fiscal-years');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @psalm-pure
     */
    public function delete(User $user, FiscalYear $fiscalYear): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @psalm-pure
     */
    public function restore(User $user, FiscalYear $fiscalYear): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @psalm-pure
     */
    public function forceDelete(User $user, FiscalYear $fiscalYear): bool
    {
        return false;
    }

    /**
     * @psalm-pure
     */
    public function replicate(User $user, FiscalYear $fiscalYear): bool
    {
        return false;
    }
}
