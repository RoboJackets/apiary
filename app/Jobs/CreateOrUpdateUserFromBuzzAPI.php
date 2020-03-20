<?php

declare(strict_types=1);

namespace App\Jobs;

use App\User;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use OITNetworkServices\BuzzAPI;
use OITNetworkServices\BuzzAPI\Resources;
use Spatie\Permission\Models\Role;

class CreateOrUpdateUserFromBuzzAPI implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public const IDENTIFIER_GTID = 'gtid';
    public const IDENTIFIER_USERNAME = 'uid';
    // @phan-suppress-next-line PhanUnreferencedPublicClassConstant
    public const IDENTIFIER_MAIL = 'email';
    public const IDENTIFIER_USER = 'user';

    /**
     * The number of attempts for this job.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * The identifier to search for the account with.
     *
     * @var string
     */
    private $identifier;

    /**
     * The value of the identifier to search for the account with.
     *
     * @var string|int
     */
    private $value;

    /**
     * Create a new job instance.
     *
     * @param string|int|\App\User $value
     */
    public function __construct(string $identifier, $value)
    {
        // This exists so we can easily migrate to searching by a different identifier in the future. The is_int call
        // is necessary to avoid calling is_a with an integer value.
        if (self::IDENTIFIER_USER === $identifier || (! is_int($value) && is_a($value, User::class))) {
            $identifier = self::IDENTIFIER_USERNAME;
            $value = $value->uid;
        }
        $this->identifier = $identifier;
        $this->value = $value;
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

        $accountsResponse = BuzzAPI::select(
            'gtGTID',
            'mail',
            'sn',
            'givenName',
            'eduPersonPrimaryAffiliation',
            'gtPrimaryGTAccountUsername',
            'gtAccountEntitlement',
            'uid'
        )->from(Resources::GTED_ACCOUNTS)
        ->where([$this->identifier => $this->value])
        ->get();

        if (! $accountsResponse->isSuccessful()) {
            throw new Exception('GTED accounts search failed with message '.$accountsResponse->errorInfo()->message);
        }
        $numResults = count($accountsResponse->json->api_result_data);
        if (0 === $numResults) {
            throw new Exception('GTED accounts search was successful but gave no results');
        }

        $account = $accountsResponse->first();
        // If there's multiple results, find the one for their primary GT account. If there's only one (we're searching
        // by the uid or GUID of that account), just use that one.
        if (1 !== $numResults) {
            $primaryUid = $account->gtPrimaryGTAccountUsername;
            $account = collect($accountsResponse->json->api_result_data)->firstWhere('uid', $primaryUid);
        }

        $user = User::where('uid', $account->uid)->first();
        $userIsNew = null === $user;
        if ($userIsNew) {
            $user = new User();
            $user->create_reason = 'buzzapi_job';
            $user->is_service_account = false;
            $user->has_ever_logged_in = false;
        }
        if ($user->is_service_account) {
            throw new Exception('BuzzAPI job attempted to create/update an account for an existing service account');
        }
        $user->uid = $account->uid;
        $user->gtid = $account->gtGTID;
        $user->gt_email = $account->mail;
        $user->first_name = $account->givenName;
        $user->last_name = $account->sn;
        $user->primary_affiliation = $account->eduPersonPrimaryAffiliation;
        $user->save();
        $user->syncMajorsFromAccountEntitlements($account->gtAccountEntitlement);

        // Initial role assignment
        if (! $userIsNew && 0 !== $user->roles->count()) {
            return;
        }

        $role = Role::where('name', 'non-member')->first();
        if (null !== $role) {
            $user->assignRole($role);

            return;
        }
        Log::error(self::class."Role 'non-member' not found for assignment to ".$user->uid);
    }
}
