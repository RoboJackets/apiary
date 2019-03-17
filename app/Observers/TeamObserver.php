<?php

use App\Team;
use App\User;
use App\Jobs\PushToJedi;

namespace App\Observers;

class TeamObserver
{
    public function hasManyCreated(Team $team, Model $user) {
        PushToJedi::dispatch($user);
    }
}
