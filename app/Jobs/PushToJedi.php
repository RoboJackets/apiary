<?php

namespace App\Jobs;

use App\User;
use GuzzleHTTP\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PushToJedi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        if (null === config('jedi.endpoint') || null === config('jedi.token')) {
            $this->delete();
        }
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $send = [];
        $send['uid'] = $this->user->uid;
        $send['first_name'] = $this->user->preferred_first_name;
        $send['last_name'] = $this->user->last_name;
        $send['is_access_active'] = $this->user->is_access_active;
        $send['teams'] = [];

        foreach ($this->user->teams as $team) {
            $send['teams'][] = $team->name;
        }

        $client = new Client(
            [
                'headers' => [
                    'User-Agent' => 'Apiary on '.config('app.url'),
                    'Authorization' => 'Bearer '.config('jedi.token'),
                ],
            ]
        );

        $response = $client->request('POST', config('jedi.endpoint'), ['json' => $send]);

        if (200 !== $response->getStatusCode()) {
            throw new \Exception(
                'Sending data to JEDI failed with HTTP response code '.$response->getStatusCode()
            );
        }
    }
}
