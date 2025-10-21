<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\PruneIndexingNotificationsInNova;
use App\Models\Airport;
use App\Models\Attendance;
use App\Models\DuesTransaction;
use App\Models\Event;
use App\Models\Team;
use App\Models\Travel;
use App\Models\TravelAssignment;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * Imports all models into Scout.
 *
 * @phan-suppress PhanUnreferencedClass
 */
class ImportAllModels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scout:import-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import all supported models into the search index';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->call('scout:import', [
            'model' => Airport::class,
        ]);

        $this->call('scout:import', [
            'model' => Attendance::class,
        ]);

        $this->call('scout:import', [
            'model' => DuesTransaction::class,
        ]);

        $this->call('scout:import', [
            'model' => Event::class,
        ]);

        $this->call('scout:import', [
            'model' => Sponsor::class,
        ]);

        $this->call('scout:import', [
            'model' => SponsorDomain::class,
        ]);

        $this->call('scout:import', [
            'model' => SponsorUser::class,
        ]);

        $this->call('scout:import', [
            'model' => Team::class,
        ]);

        $this->call('scout:import', [
            'model' => Travel::class,
        ]);

        $this->call('scout:import', [
            'model' => TravelAssignment::class,
        ]);

        $this->call('scout:import', [
            'model' => User::class,
        ]);

        PruneIndexingNotificationsInNova::dispatch();

        return 0;
    }
}
