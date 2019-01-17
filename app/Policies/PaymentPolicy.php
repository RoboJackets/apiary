<?php

namespace App\Policies;

use App\User;
use App\Payment;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Payment $resource)
    {
        return $user->can('read-payments');
    }

    public function viewAny(User $user)
    {
        return $user->can('read-payments');
    }

    public function create(User $user)
    {
        return false; // not manually
    }

    public function update(User $user, Payment $resource)
    {
        return $user->can('update-payments');
    }

    public function delete(User $user, Payment $resource)
    {
        return $user->can('delete-payments');
    }

    public function restore(User $user, Payment $resource)
    {
        return $user->can('delete-payments');
    }

    public function forceDelete(User $user, Payment $resource)
    {
        return false;
    }
}
