<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.RequireTernaryOperator.TernaryOperatorNotUsed

namespace App\Util;

use App\Exceptions\MissingAttribute;
use App\Jobs\CreateOrUpdateUserFromBuzzAPI;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Subfission\Cas\Facades\Cas;

class CasUser
{
    /**
     * Creates the logged in CAS user if they don't already exist, or update attributes if they do.
     */
    public static function createOrUpdate(): User
    {
        $attrs = [
            'gtGTID',
            'email_primary',
            'givenName',
            'sn',
            'eduPersonPrimaryAffiliation',
            'eduPersonScopedAffiliation',
            'authenticationDate',
        ];
        if (Cas::isMasquerading()) {
            $masq_attrs = [];
            foreach ($attrs as $attr) {
                $masq_attrs[$attr] = config('cas.cas_masquerade_'.$attr);
            }
            Cas::setAttributes($masq_attrs);
        }

        if (config('features.sandbox-mode') !== true) {
            foreach ($attrs as $attr) {
                if (! Cas::hasAttribute($attr) || Cas::getAttribute($attr) === null) {
                    throw new MissingAttribute('Missing attribute '.$attr.' from CAS for user '.Cas::user());
                }
            }
        }

        //User is starting a new session, so let's update data from CAS
        //Sadly we can't use updateOrCreate here because of $guarded in the User model
        $user = User::where('uid', Cas::user())->first();
        if ($user === null) {
            $user = new User();
            $user->create_reason = 'cas_login';
            $user->is_service_account = false;
        }
        if ($user->is_service_account) {
            abort(403);
        }
        $user->uid = Cas::user();
        if (config('features.sandbox-mode') === true && Cas::getAttribute('gtGTID') === null) {
            $user->gtid = 999999999;
        } else {
            $user->gtid = Cas::getAttribute('gtGTID');
        }
        $user->gt_email = Cas::getAttribute('email_primary');
        $user->first_name = Cas::getAttribute('givenName');
        $user->last_name = Cas::getAttribute('sn');
        $user->primary_affiliation = Cas::getAttribute('eduPersonPrimaryAffiliation');
        $user->has_ever_logged_in = true;
        $user->save();

        $standing_count = $user->syncClassStandingFromEduPersonScopedAffiliation(
            Cas::getAttribute('eduPersonScopedAffiliation')
        );

        if ($user->primary_affiliation === 'student' && $standing_count !== 1) {
            Log::warning(
                self::class.': User '.$user->uid
                .' has primary affiliation of student but '.$standing_count.' class standings. Check data integrity.'
            );
        }

        if (! Cas::hasAttribute('gtCurriculum') || Cas::getAttribute('gtCurriculum') === null) {
            $user->syncMajorsFromGtCurriculum([]);
        } else {
            if (is_array(Cas::getAttribute('gtCurriculum'))) {
                $major_count = $user->syncMajorsFromGtCurriculum(Cas::getAttribute('gtCurriculum'));
            } else {
                $major_count = $user->syncMajorsFromGtCurriculum([Cas::getAttribute('gtCurriculum')]);
            }

            if ($user->primary_affiliation === 'student' && $major_count !== 1) {
                Log::warning(
                    self::class.': User '.$user->uid
                    .' has primary affiliation of student but no majors. Check data integrity.'
                );
            }
        }

        if ($user->roles_count === 0 && $user->is_active === false) {
            $user->assignRole('non-member');
        } elseif ($user->is_active) {
            $user->removeRole('non-member');
            $user->assignRole('member');
        }

        if (config('features.sandbox-mode') !== true && ! Cas::isMasquerading()) {
            CreateOrUpdateUserFromBuzzAPI::dispatch(CreateOrUpdateUserFromBuzzAPI::IDENTIFIER_USER, $user, 'cas_login');
        }

        return $user;
    }
}
