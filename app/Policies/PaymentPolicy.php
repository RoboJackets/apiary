<?php

declare(strict_types=1);

namespace App\Policies;

use App\Payment;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Payment $resource): bool
    {
        return $user->can('read-payments');
    }

    public function viewAny(User $user): bool
    {
        return $user->can('read-payments');
    }

    public function create(User $user): bool
    {
        return false; // not manually
    }

    public function update(User $user, Payment $resource): bool
    {
        return false; // not manually
    }

    public function delete(User $user, Payment $resource): bool
    {
        return $user->can('delete-payments');
    }

    public function restore(User $user, Payment $resource): bool
    {
        return $user->can('delete-payments');
    }

    public function forceDelete(User $user, Payment $resource): bool
    {
        return false;
    }
}
