<?php

namespace App\Observers;

use App\Team;
use App\User;
use App\Jobs\PushToJedi;

class TeamObserver
{
    public function belongsToManyAttached(string $relation, Team $team, array $ids)
    {
        PushToJedi::dispatch(User::find($ids[0]))->onQueue('jedi');
    }

    public function belongsToManyDetached(string $relation, Team $team, array $ids)
    {
        PushToJedi::dispatch(User::find($ids[0]))->onQueue('jedi');
    }
}
