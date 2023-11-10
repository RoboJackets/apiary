<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class CreateOrUpdateUserFromBuzzAPI implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public const IDENTIFIER_GTID = 'gtid';

    public const IDENTIFIER_USERNAME = 'uid';

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
    public $tries = 2;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private string $identifier,
        private readonly string|int|User $value,
        private readonly string $reason
    ) {
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

        $client = new Client([
            'base_uri' => 'https://'.config('buzzapi.host').'/apiv3/',
            'allow_redirects' => false,
            'connect_timeout' => config('buzzapi.connect_timeout'),
            'timeout' => config('buzzapi.timeout'),
        ]);

        $response = $client->post(
            'central.iam.gted.accounts/search',
            [
                'json' => [
                    'api_app_id' => config('buzzapi.app_id'),
                    'api_app_password' => config('buzzapi.app_password'),
                    'api_request_mode' => 'sync',
                    'api_log_level' => config('buzzapi.default_log_level'),
                    $this->identifier => $searchValue,
                    'requested_attributes' => [
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
                        'gtCurriculum',
                    ],
                ],
            ]
        );

        if ($response->getStatusCode() !== 200) {
            throw new Exception('BuzzAPI returned status code '.$response->getStatusCode());
        }

        $json = json_decode($response->getBody()->getContents());

        if (! property_exists($json, 'api_result_data')) {
            Log::warning(self::class.': '.$response->getBody()->getContents());
            throw new Exception('BuzzAPI returned an eror');
        }
        $numResults = count($json->api_result_data);
        if ($numResults === 0) {
            throw new Exception('GTED accounts search was successful but gave no results for '.$searchValue);
        }

        // If there's multiple results, find the one for their primary GT account or of the User we're searching for.
        // If there's only one (we're searching by the uid of that account), just use that one.
        $searchUid ??= $json->api_result_data[0]->gtPrimaryGTAccountUsername;
        $account = collect($json->api_result_data)->firstWhere('uid', $searchUid);

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

        Log::error(self::class.": Role 'non-member' not found for assignment to ".$user->uid);
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<string>
     */
    public function tags(): array
    {
        return [
            $this->identifier.':'.(
                $this->identifier === self::IDENTIFIER_USER ? $this->value->uid : $this->value
            ),
        ];
    }
}
