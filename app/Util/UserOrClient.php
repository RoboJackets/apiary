<?php

declare(strict_types=1);

namespace App\Util;

use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Guard;

class UserOrClient
{
    public static function can(string $permission): bool
    {
        if (Auth::user() !== null) {
            return Auth::user()->can($permission);
        } elseif (Guard::getPassportClient(null) !== null) {
            return Guard::getPassportClient(null)->can($permission);
        } else {
            return false;
        }
    }
}
