<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Team;
use App\User;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PushToJedi implements ShouldQueue
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
     * The user that will be sent to JEDI.
     *
     * @var \App\User
     */
    private $user;

    /**
     * The name of the class that caused the push to be run.
     *
     * @var string
     */
    private $model_class;

    /**
     * The ID of the model that caused the push to be run.
     *
     * @var int
     */
    private $model_id;

    /**
     * A description of the event that caused the push to be run.
     *
     * @var string
     */
    private $model_event;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, string $model_class, int $model_id, string $model_event)
    {
        $this->user = $user;
        $this->model_class = $model_class;
        $this->model_id = $model_id;
        $this->model_event = $model_event;
        $this->tries = 1;
        $this->queue = 'jedi';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (null === config('jedi.host') || null === config('jedi.token')) {
            return;
        }

        $lastAttendance = $this->user->attendance()->where('attendable_type', Team::class)
            ->orderBy('created_at', 'desc')->first();

        $send = [
            'uid' => strtolower($this->user->uid),
            'first_name' => $this->user->preferred_first_name,
            'last_name' => $this->user->last_name,
            'is_access_active' => $this->user->is_access_active,
            'github_username' => $this->user->github_username,
            'google_accounts' => [$this->user->gmail_address],
            'model_class' => $this->model_class,
            'model_id' => $this->model_id,
            'model_event' => $this->model_event,
            'last_attendance_time' => null !== $lastAttendance ? $lastAttendance->created_at : null,
            'last_attendance_id' => null !== $lastAttendance ? $lastAttendance->id : null,
            'teams' => [],
            'project_manager_of_teams' => [],
            'exists_in_sums' => $this->user->exists_in_sums,
            'clickup_email' => $this->user->clickup_email,
            'clickup_id' => $this->user->clickup_id,
            'clickup_invite_pending' => $this->user->clickup_invite_pending,
        ];

        foreach ($this->user->teams as $team) {
            $send['teams'][] = $team->name;
        }

        foreach ($this->user->manages as $team) {
            $send['project_manager_of_teams'][] = $team->name;
        }

        if (in_array('G Suite', $send['teams'], true)) {
            $send['google_accounts'][] = $this->user->preferred_first_name.'.'.$this->user->last_name
                .'@robojackets.org';
        }

        $send['google_accounts'] = array_unique($send['google_accounts'], SORT_STRING);

        $client = new Client(
            [
                'headers' => [
                    'User-Agent' => 'Apiary on '.config('app.url'),
                    'Authorization' => 'Bearer '.config('jedi.token'),
                    'Accept' => 'application/json',
                ],
                'allow_redirects' => false,
            ]
        );

        $response = $client->request('POST', config('jedi.host').'/api/v1/apiary', ['json' => $send]);

        if (200 !== $response->getStatusCode()) {
            throw new Exception(
                'Sending data to JEDI failed with HTTP response code '.$response->getStatusCode()
            );
        }
    }
}
