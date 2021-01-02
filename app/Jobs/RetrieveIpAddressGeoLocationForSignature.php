<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Signature;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RetrieveIpAddressGeoLocationForSignature implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of attempts for this job.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * The signature to update.
     *
     * @var \App\Models\Signature
     */
    private $signature;

    /**
     * Create a new job instance.
     */
    public function __construct(Signature $signature)
    {
        $this->signature = $signature;
        $this->queue = 'ipstack';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $client = new Client(
            [
                'base_uri' => config('ipstack.base_url'),
                'headers' => [
                    'User-Agent' => 'Apiary on '.config('app.url'),
                ],
                'http_errors' => true,
                'allow_redirects' => false,
            ]
        );

        $response = $client->get(
            $this->signature->ip_address,
            [
                'query' => [
                    'access_key' => config('ipstack.api_key'),
                    'output' => 'json',
                ],
            ]
        );

        $this->signature->ip_address_location_estimate = json_decode($response->getBody()->getContents());
        $this->signature->save();
    }
}
