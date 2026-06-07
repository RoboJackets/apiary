<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Sleep;
use Meilisearch\Client;
use Meilisearch\Contracts\TasksQuery;
use RuntimeException;

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
     * How long to wait for indexing to complete before dumping, in seconds.
     */
    private const int INDEXING_TIMEOUT_IN_SECONDS = 1800;

    /**
     * How frequently to poll for indexing completion, in seconds.
     */
    private const int INDEXING_POLL_INTERVAL_IN_SECONDS = 5;

    /**
     * The number of consecutive idle polls required before indexing is considered complete.
     */
    private const int INDEXING_STABLE_CHECKS = 2;

    /**
     * Execute the console command.
     */
    public function handle(Client $client): int
    {
        $this->waitForIndexingToComplete($client);

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

    /**
     * Wait until queued indexing jobs have drained and Meilisearch has finished processing every task.
     *
     * Scout queues indexing onto the configured queue, which Horizon then turns into asynchronous
     * Meilisearch tasks, so both layers must be idle before the dump captures the complete index.
     */
    private function waitForIndexingToComplete(Client $client): void
    {
        $queue = config('scout.queue.queue');
        $waited = 0;
        $stableChecks = 0;

        while (true) {
            $queuedJobs = Queue::size($queue);
            $pendingTasks = $client->getTasks(
                (new TasksQuery())->setStatuses(['enqueued', 'processing'])->setLimit(1)
            )->count();

            if ($queuedJobs === 0 && $pendingTasks === 0) {
                $stableChecks++;

                if ($stableChecks >= self::INDEXING_STABLE_CHECKS) {
                    $this->comment('Indexing complete.');

                    return;
                }
            } else {
                $stableChecks = 0;

                $this->info(
                    'Waiting for indexing to complete: '.$queuedJobs.' queued job(s), '.
                    $pendingTasks.' pending task(s)...'
                );
            }

            if ($waited >= self::INDEXING_TIMEOUT_IN_SECONDS) {
                throw new RuntimeException('Timed out waiting for indexing to complete before creating a dump.');
            }

            Sleep::for(self::INDEXING_POLL_INTERVAL_IN_SECONDS)->seconds();

            $waited += self::INDEXING_POLL_INTERVAL_IN_SECONDS;
        }
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
