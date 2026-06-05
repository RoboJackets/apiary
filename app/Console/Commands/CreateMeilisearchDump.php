<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Meilisearch\Client;

/**
 * Creates a Meilisearch dump and prunes older dumps.
 *
 * @phan-suppress PhanUnreferencedClass
 */
class CreateMeilisearchDump extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meilisearch:dump';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Meilisearch dump and prune older dumps';

    /**
     * The number of dumps to keep when pruning.
     */
    private const int DUMPS_TO_KEEP = 1;

    /**
     * How long to wait for the dump task to complete, in milliseconds.
     */
    private const int TIMEOUT_IN_MS = 1800000;

    /**
     * How frequently to poll the dump task for completion, in milliseconds.
     */
    private const int INTERVAL_IN_MS = 2000;

    /**
     * Execute the console command.
     */
    public function handle(Client $client): int
    {
        $this->info('Requesting Meilisearch dump...');

        $task = $client->createDump();

        $this->info('Waiting for dump task '.$task['taskUid'].' to complete...');

        $result = $client->waitForTask($task['taskUid'], self::TIMEOUT_IN_MS, self::INTERVAL_IN_MS);

        if ($result['status'] !== 'succeeded') {
            $this->error('Dump task finished with status `'.$result['status'].'`.');

            return 1;
        }

        $this->comment('Dump created successfully.');

        $this->pruneOldDumps();

        return 0;
    }

    private function pruneOldDumps(): void
    {
        $path = config('scout.meilisearch.dump-path');

        if (! is_dir($path)) {
            return;
        }

        $dumps = collect(glob($path.'/*.dump') ?: [])
            ->sortByDesc(static fn (string $file): int => filemtime($file))
            ->values();

        $dumps->slice(self::DUMPS_TO_KEEP)->each(function (string $file): void {
            $this->info('Pruning old dump `'.$file.'`.');

            unlink($file);
        });
    }
}
