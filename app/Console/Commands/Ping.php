<?php

declare(strict_types=1);

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;

/**
 * Calls the /ping endpoint on this app to see if it is working.
 * Used during deployments to determine if Enlightn HTTP-based checks should be run.
 *
 * @phan-suppress PhanUnreferencedClass
 */
class Ping extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ping';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a request to the /ping endpoint';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $client = new Client(
            [
                'allow_redirects' => false,
                'connect_timeout' => 1,
                'read_timeout' => 1,
                'timeout' => 1,
            ]
        );

        $response = $client->request('GET', config('app.url').'/ping');

        return $response->getStatusCode() === 200 ? 0 : 1;
    }
}
