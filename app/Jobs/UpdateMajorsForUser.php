<?php

declare(strict_types=1);

namespace App\Jobs;

use App\User;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use OITNetworkServices\BuzzAPI;
use OITNetworkServices\BuzzAPI\Resources;

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
     * The user for which to update majors.
     *
     * @var \App\User
     */
    private $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
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

        if (null === $this->user) {
            throw new Exception('Attempted to run UpdateMajorsForUser without an existing user');
        }
        if ($this->user->is_service_account) {
            throw new Exception('Attempted to run UpdateMajorsForUser on a service account');
        }

        $accountResponse = BuzzAPI::select('gtAccountEntitlement')
            ->from(Resources::GTED_ACCOUNTS)
            ->where(['uid' => $this->user->uid])
            ->get();

        if (! $accountResponse->isSuccessful()) {
            throw new Exception('GTED account search failed with message '.$accountResponse->errorInfo()->message);
        }
        if (0 === count($accountResponse->json->api_result_data)) {
            throw new Exception('GTED account search was successful but gave no results');
        }

        $account = $accountResponse->first();

        $this->user->syncMajorsFromAccountEntitlements($account->gtAccountEntitlement);
    }
}
