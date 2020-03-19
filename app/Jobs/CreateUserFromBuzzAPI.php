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
use Illuminate\Support\Facades\Log;
use OITNetworkServices\BuzzAPI;
use OITNetworkServices\BuzzAPI\Resources;
use Spatie\Permission\Models\Role;

class CreateUserFromBuzzAPI implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public const IDENTIFIER_GTID = 'gtid';
    // @phan-suppress-next-line PhanUnreferencedPublicClassConstant
    public const IDENTIFIER_USERNAME = 'uid';
    // @phan-suppress-next-line PhanUnreferencedPublicClassConstant
    public const IDENTIFIER_MAIL = 'email';

    /**
     * The number of attempts for this job.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * The identifier to search for the person with.
     *
     * @var string
     */
    private $identifier;

    /**
     * The value of the identifier to search for the person with.
     *
     * @var string|int
     */
    private $value;

    /**
     * Create a new job instance.
     *
     * @param string|int $value
     */
    public function __construct(string $identifier, $value)
    {
        $this->identifier = $identifier;
        $this->value = $value;
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

        $peopleResponse = BuzzAPI::select(
                'gtGTID', 'mail', 'sn', 'givenName', 'eduPersonPrimaryAffiliation', 'gtPrimaryGTAccountUsername'
            )->from(Resources::GTED_PEOPLE)
            ->where([$this->identifier => $this->value])
            ->get();

        if (! $peopleResponse->isSuccessful()) {
            throw new Exception('GTED people search failed with message '.$peopleResponse->errorInfo()->message);
        }
        if (0 === count($peopleResponse->json->api_result_data)) {
            throw new Exception('GTED people search was successful but gave no results');
        }

        $person = $peopleResponse->first();

        $user = User::where('uid', $person->gtPrimaryGTAccountUsername)->first();
        if (null === $user) {
            $user = new User();
            $user->create_reason = 'buzzapi-job';
            $user->is_service_account = false;
        }
        if ($user->is_service_account) {
            throw new Exception('BuzzAPI job attempted to create an account for an existing service account');
        }
        $user->uid = $person->gtPrimaryGTAccountUsername;
        $user->gtid = $person->gtGTID;
        $user->gt_email = $person->mail;
        $user->first_name = $person->givenName;
        $user->last_name = $person->sn;
        $user->primary_affiliation = $person->eduPersonPrimaryAffiliation;
        $user->has_ever_logged_in = false;
        $user->save();

        // Initial role assignment
        if ($user->wasRecentlyCreated || 0 === $user->roles->count()) {
            $role = Role::where('name', 'non-member')->first();
            if (null !== $role) {
                $user->assignRole($role);
                return;
            }
            Log::error(self::class."Role 'non-member' not found for assignment to ".$user->uid);
        }
    }
}
