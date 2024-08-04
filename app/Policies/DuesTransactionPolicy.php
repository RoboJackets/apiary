<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DuesTransaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DuesTransactionPolicy
{
    use HandlesAuthorization;

    public function view(User $user, DuesTransaction $transaction): bool
    {
        return $user->can('read-dues-transactions');
    }

    public function viewAny(User $user): bool
    {
        return $user->can('read-dues-transactions');
    }

    public function create(User $user): bool
    {
        return $user->can('create-dues-transactions');
    }

    public function update(User $user, DuesTransaction $transaction): bool
    {
        return false;
    }

    public function delete(User $user, DuesTransaction $transaction): bool
    {
        return false;
    }

    public function restore(User $user, DuesTransaction $transaction): bool
    {
        return false;
    }

    public function forceDelete(User $user, DuesTransaction $transaction): bool
    {
        return false;
    }

    public function attachMerchandise(User $user, DuesTransaction $transaction): bool
    {
        return $user->hasRole('admin');
    }

    public function attachAnyMerchandise(User $user, DuesTransaction $transaction): bool
    {
        return $user->hasRole('admin');
    }

    public function detachMerchandise(User $user, DuesTransaction $transaction): bool
    {
        return $user->hasRole('admin');
    }

    public function replicate(User $user, DuesTransaction $transaction): bool
    {
        return false;
    }
}
