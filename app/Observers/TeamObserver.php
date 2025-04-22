<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\PushToJedi;
use App\Models\Team;
use Spatie\ResponseCache\Facades\ResponseCache;

class TeamObserver
{
    public function saved(Team $team): void
    {
        ResponseCache::clear();

        if ($team->projectManager !== null) {
            PushToJedi::dispatch($team->projectManager, Team::class, $team->id, 'saved');
        }
    }
}
