<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;

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
     *
     * @return int
     */
    public function handle()
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

        if (200 !== $response->getStatusCode()) {
            return 1;
        } else {
            return 0;
        }
    }
}
