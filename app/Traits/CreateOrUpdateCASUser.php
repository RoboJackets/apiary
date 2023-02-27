<?php

declare(strict_types=1);

namespace App\Traits;

use App\Jobs\CreateOrUpdateUserFromBuzzAPI;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

trait CreateOrUpdateCASUser
{
    /**
     * CAS library interface.
     *
     * @var \Subfission\Cas\CasManager
     */
    protected $cas;

    public function __construct()
    {
        $this->cas = app('cas');
    }

    /**
     * Creates the logged in CAS user if they don't already exist, or update attributes if they do.
     */
    public function createOrUpdateCASUser(): User
    {
        $attrs = [
            'gtGTID',
            'email_primary',
            'givenName',
            'sn',
            'eduPersonPrimaryAffiliation',
            'eduPersonScopedAffiliation',
        ];
        if ($this->cas->isMasquerading()) {
            $masq_attrs = [];
            foreach ($attrs as $attr) {
                $masq_attrs[$attr] = config('cas.cas_masquerade_'.$attr);
            }
            $this->cas->setAttributes($masq_attrs);
        }

        if (config('features.demo-mode') === null) {
            foreach ($attrs as $attr) {
                if (! $this->cas->hasAttribute($attr) || $this->cas->getAttribute($attr) === null) {
                    throw new Exception('Missing attribute '.$attr.' from CAS');
                }
            }
        }

        //User is starting a new session, so let's update data from CAS
        //Sadly we can't use updateOrCreate here because of $guarded in the User model
        $user = User::where('uid', $this->cas->user())->first();
        if ($user === null) {
            $user = new User();
            $user->create_reason = 'cas_login';
            $user->is_service_account = false;
        }
        if ($user->is_service_account) {
            abort(403);
        }
        $user->uid = $this->cas->user();
        $user->gtid = config('features.demo-mode') === null ? $this->cas->getAttribute('gtGTID') : 999999999;
        $user->gt_email = $this->cas->getAttribute('email_primary');
        $user->first_name = $this->cas->getAttribute('givenName');
        $user->last_name = $this->cas->getAttribute('sn');
        $user->primary_affiliation = $this->cas->getAttribute('eduPersonPrimaryAffiliation');
        $user->has_ever_logged_in = true;
        $user->save();

        $standing_count = $user->syncClassStandingFromEduPersonScopedAffiliation(
            $this->cas->getAttribute('eduPersonScopedAffiliation')
        );

        if ($user->primary_affiliation === 'student' && $standing_count !== 1) {
            Log::warning(
                self::class.': User '.$user->uid
                .' has primary affiliation of student but '.$standing_count.' class standings. Check data integrity.'
            );
        }

        if (! $this->cas->hasAttribute('gtCurriculum') || $this->cas->getAttribute('gtCurriculum') === null) {
            $user->syncMajorsFromGtCurriculum([]);
        } else {
            $major_count = $user->syncMajorsFromGtCurriculum($this->cas->getAttribute('gtCurriculum'));

            if ($user->primary_affiliation === 'student' && $major_count !== 1) {
                Log::warning(
                    self::class.': User '.$user->uid
                    .' has primary affiliation of student but no majors. Check data integrity.'
                );
            }
        }

        //Initial Role Assignment
        if ($user->wasRecentlyCreated || $user->roles->count() === 0) {
            $role = Role::where('name', 'non-member')->first();
            if ($role !== null) {
                $user->assignRole($role);
            } else {
                Log::error(self::class."Role 'non-member' not found for assignment to ".$user->uid);
            }
        }

        //Role update based on active status (in case it didn't happen elsewhere)
        if ($user->is_active === true && $user->hasRole('non-member')) {
            Log::info(self::class.': Updating role membership for '.$user->uid);
            $user->removeRole('non-member');
            $role_member = Role::where('name', 'member')->first();
            if ($role_member !== null && ! $user->hasRole('member')) {
                $user->assignRole($role_member);
            } else {
                Log::error(self::class.": Role 'member' not found for assignment to ".$user->uid);
            }
        }

        if (config('features.demo-mode') === null && ! $this->cas->isMasquerading()) {
            CreateOrUpdateUserFromBuzzAPI::dispatch(CreateOrUpdateUserFromBuzzAPI::IDENTIFIER_USER, $user, 'cas_login');
        }

        return $user;
    }
}
