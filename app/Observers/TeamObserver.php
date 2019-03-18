<?php

namespace App\Observers;

use App\Team;
use App\Jobs\PushToJedi;

class TeamObserver
{
    public function belongsToManyAttached($relation, $related)
    {
        PushToJedi::dispatch($related);
    }

    public function belongsToManyDetached($relation, $related)
    {
        PushToJedi::dispatch($related);
    }
}
