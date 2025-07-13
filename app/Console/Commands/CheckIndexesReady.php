<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Meilisearch\Client;


/**
 * Check if all expected indexes are present in Meilisearch.
 *
 * @phan-suppress PhanUnreferencedClass
 */
class CheckIndexesReady extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scout:indexes-ready';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if indexes are ready';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $meilisearch = new Client(url: config('scout.meilisearch.host'), apiKey: config('scout.meilisearch.key'));

        return $meilisearch->getIndexes()->count() >= count(config('scout.meilisearch.index-settings')) ? 0 : 1;
    }
}
