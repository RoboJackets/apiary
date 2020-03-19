<?php

declare(strict_types=1);

namespace App\Jobs;

use App\User;
use OITNetworkServices\BuzzAPI;
use OITNetworkServices\BuzzAPI\Resources;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateMajorsForUser implements ShouldQueue
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
     * The username for which to update majors.
     *
     * @var string
     */
    private $username;

    /**
     * Create a new job instance.
     */
    public function __construct(string $username)
    {
        $this->username = $username;
        $this->tries = 1;
        $this->queue = 'buzzapi';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (null === config('buzzapi.app_password')) {
            return;
        }

        $user = User::where('uid', $person->gtPrimaryGTAccountUsername)->first();
        if (null === $user) {
            throw new Exception('Attempted to run UpdateMajorsForUser without an existing user');
        }
        if ($user->is_service_account) {
            throw new Exception('Attempted to run UpdateMajorsForUser on a service account');
        }

        $accountResponse = BuzzAPI::select(['gtAccountEntitlement'])
            ->from(Resources::GTED_ACCOUNT)
            ->where(['uid' => $this->username])
            ->get();

        if (! $accountResponse->isSuccessful) {
            throw new Exception('GTED account search failed with message '.$accountResponse->errorInfo()->message);
        }
        if (0 === count($accountResponse->json->api_result_data)) {
            throw new Exception('GTED account search was successful but gave no results');
        }

        $account = $accountResponse->first();

        $user->syncMajorsFromAccountEntitlements($account->gtAccountEntitlement);
    }
}
