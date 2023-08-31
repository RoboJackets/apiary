<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Team;
use App\Models\User;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PushToJedi implements ShouldBeUnique, ShouldQueue
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
     * Create a new job instance.
     */
    public function __construct(
        private readonly User $user,
        private readonly string $model_class,
        private readonly int $model_id,
        private readonly string $model_event
    ) {
        $this->queue = 'jedi';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (config('jedi.host') === null || config('jedi.token') === null) {
            return;
        }

        // Skip users who have never logged in as they will not have access to anything. This allows running on every
        // user but saving some time.
        if (! $this->user->has_ever_logged_in) {
            return;
        }

        $lastAttendance = $this->user->attendance()->where('attendable_type', Team::getMorphClassStatic())
            ->orderByDesc('created_at')->first();

        $send = [
            'username' => strtolower($this->user->uid),
            'first_name' => $this->user->preferred_first_name,
            'last_name' => $this->user->last_name,
            'is_access_active' => $this->user->is_access_active,
            'github_username' => $this->user->github_username,
            'google_account' => $this->user->gmail_address,
            'model_class' => $this->model_class,
            'model_id' => $this->model_id,
            'model_event' => $this->model_event,
            'last_attendance_time' => $lastAttendance?->created_at,
            'last_attendance_id' => $lastAttendance?->id,
            'teams' => [],
            'project_manager_of_teams' => [],
            'exists_in_sums' => $this->user->exists_in_sums,
            'clickup_email' => $this->user->clickup_email,
            'clickup_id' => $this->user->clickup_id,
            'clickup_invite_pending' => $this->user->clickup_invite_pending,
            'signed_latest_agreement' => $this->user->signed_latest_agreement,
        ];

        foreach ($this->user->teams as $team) {
            $send['teams'][] = $team->name;
        }

        foreach ($this->user->manages as $team) {
            $send['project_manager_of_teams'][] = $team->name;
        }

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

        if ($response->getStatusCode() !== 202) {
            throw new Exception(
                'Sending data to JEDI failed with HTTP response code '.$response->getStatusCode()
            );
        }
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return strval($this->user->id);
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<string>
     */
    public function tags(): array
    {
        return [
            'user:'.$this->user->uid,
        ];
    }
}
