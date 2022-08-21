<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
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

class CreateOrUpdateUserFromBuzzAPI implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public const IDENTIFIER_GTID = 'gtid';
    public const IDENTIFIER_USERNAME = 'uid';
    // @phan-suppress-next-line PhanUnreferencedPublicClassConstant
    public const IDENTIFIER_MAIL = 'email';
    public const IDENTIFIER_USER = 'user';
    public const IDENTIFIER_GTDIRGUID = 'gtPersonDirectoryID';

    public const EXPECTED_ATTRIBUTES = [
        'uid',
        'gtGTID',
        'mail',
        'givenName',
        'sn',
        'eduPersonPrimaryAffiliation',
        'gtPersonDirectoryId',
        'gtPrimaryGTAccountUsername',
        'eduPersonScopedAffiliation',
    ];

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
     * @var string|int|\App\Models\User
     */
    private $value;

    /**
     * The reason the user is being created (or null if the user is not being created).
     *
     * @var string
     */
    private $reason;

    /**
     * Create a new job instance.
     */
    public function __construct(string $identifier, string|int|\App\Models\User $value, string $reason)
    {
        $this->identifier = $identifier;
        $this->value = $value;
        $this->reason = $reason;
        $this->queue = 'buzzapi';
    }

    /**
     * Execute the job.
     *
     * @phan-suppress PhanTypeExpectedObjectPropAccessButGotNull,PhanTypeMismatchArgumentInternal
     */
    public function handle(): void
    {
        if (config('buzzapi.app_password') === null) {
            return;
        }

        // This exists so we can easily migrate to searching by a different identifier in the future. The is_int call
        // is necessary to avoid calling is_a with an integer value.
        $searchUid = null;
        if ($this->value instanceof User) {
            $searchUid = $this->value->uid;
            if ($this->value->gtDirGUID === null) {
                $this->identifier = self::IDENTIFIER_USERNAME;
                $searchValue = $this->value->uid;
            } else {
                $this->identifier = self::IDENTIFIER_GTDIRGUID;
                $searchValue = $this->value->gtDirGUID;
            }
        } else {
            $searchValue = $this->value;
        }

        $accountsResponse = BuzzAPI::select(
            'gtGTID',
            'mail',
            'sn',
            'givenName',
            'eduPersonPrimaryAffiliation',
            'gtPrimaryGTAccountUsername',
            'uid',
            'gtEmplId',
            'gtEmployeeHomeDepartmentName',
            'eduPersonScopedAffiliation',
            'gtCurriculum'
        )->from(Resources::GTED_ACCOUNTS)
        // @phan-suppress-next-line PhanTypeMismatchArgument
        ->where([$this->identifier => $searchValue])
        ->get();

        if (! $accountsResponse->isSuccessful()) {
            throw new Exception('GTED accounts search failed with message '.$accountsResponse->errorInfo()->message);
        }
        $numResults = count($accountsResponse->json->api_result_data);
        if ($numResults === 0) {
            throw new Exception('GTED accounts search was successful but gave no results for '.$searchValue);
        }

        // If there's multiple results, find the one for their primary GT account or of the User we're searching for.
        // If there's only one (we're searching by the uid of that account), just use that one.
        $searchUid ??= $accountsResponse->first()->gtPrimaryGTAccountUsername;
        $account = collect($accountsResponse->json->api_result_data)->firstWhere('uid', $searchUid);

        $user = User::where('uid', $account->uid)->first();
        $userIsNew = $user === null;
        if ($userIsNew) {
            $user = new User();
            $user->create_reason = $this->reason;
            $user->is_service_account = false;
            $user->has_ever_logged_in = false;
        }
        if ($user->is_service_account) {
            throw new Exception('BuzzAPI job attempted to create/update an account for an existing service account');
        }

        foreach (self::EXPECTED_ATTRIBUTES as $attr) {
            if (! property_exists($account, $attr)) {
                throw new Exception('Selected account for '.$searchValue.' is missing expected attribute '.$attr);
            }
        }

        $user->uid = $account->uid;
        $user->gtid = $account->gtGTID;
        $user->gt_email = $account->mail;
        $user->first_name = $account->givenName;
        $user->last_name = $account->sn;
        $user->primary_affiliation = $account->eduPersonPrimaryAffiliation;
        $user->gtDirGUID = $account->gtPersonDirectoryId;
        $user->employee_id = property_exists($account, 'gtEmplId') ? $account->gtEmplId : null;
        $user->employee_home_department = property_exists(
            $account,
            'gtEmployeeHomeDepartmentName'
        ) ? $account->gtEmployeeHomeDepartmentName : null;
        $user->save();
        $major_count = $user->syncMajorsFromGtCurriculum(
            property_exists(
                $account,
                'gtCurriculum'
            ) ? $account->gtCurriculum : []
        );
        $standing_count = $user->syncClassStandingFromEduPersonScopedAffiliation($account->eduPersonScopedAffiliation);

        if ($user->primary_affiliation === 'student' && $standing_count !== 1) {
            Log::warning(
                self::class.': User '.$user->uid
                .' has primary affiliation of student but '.$standing_count.' class standings. Check data integrity.'
            );
        }

        if ($user->primary_affiliation === 'student' && $major_count === 0) {
            Log::warning(
                self::class.': User '.$user->uid
                .' has primary affiliation of student but no majors. Check data integrity.'
            );
        }

        // Initial role assignment
        if (! $userIsNew && $user->roles->count() !== 0) {
            return;
        }

        $role = Role::where('name', 'non-member')->first();
        if ($role !== null) {
            $user->assignRole($role);

            return;
        }
        Log::error(self::class."Role 'non-member' not found for assignment to ".$user->uid);
    }
}
