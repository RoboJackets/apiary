<?php

namespace App\Nova\Actions;

use GuzzleHTTP\Client;
use Illuminate\Bus\Queueable;
use Laravel\Nova\Actions\Action;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

class SyncAccess extends Action
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        // This whole function is going to get factored out later
        // Also it will be a queued job I guess?
        foreach ($models as $user) {
            $send = [];
            $send['uid'] = $user->uid;
            $send['first_name'] = $user->preferred_first_name;
            $send['last_name'] = $user->last_name;
            $send['is_access_active'] = $user->is_access_active;
            $send['teams'] = [];

            foreach ($user->teams as $team) {
                $send['teams'][] = $team->name;
            }

            $client = new Client(
                [
                    'headers' => [
                        'User-Agent' => 'Apiary on '.config('app.url'),
                        'Accept' => 'application/json',
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

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [];
    }
}
