<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Airport;
use App\Models\User;

class AirportPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): true
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Airport $airport): true
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): false
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Airport $airport): false
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Airport $airport): false
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Airport $airport): false
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Airport $airport): false
    {
        return false;
    }
}
