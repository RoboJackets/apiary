<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Payment $payment): bool
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

    public function update(User $user, Payment $payment): bool
    {
        return false; // not manually
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $user->can('delete-payments');
    }

    public function restore(User $user, Payment $payment): bool
    {
        return $user->can('delete-payments');
    }

    public function forceDelete(User $user, Payment $payment): bool
    {
        return false;
    }

    public function replicate(User $user, Payment $payment): bool
    {
        return false;
    }
}
