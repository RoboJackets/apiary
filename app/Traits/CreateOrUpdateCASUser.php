<?php
/**
 * Created by PhpStorm.
 * User: ross
 * Date: 11/4/17
 * Time: 9:30 AM.
 */

namespace App\Traits;

use Log;
use App\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

trait CreateOrUpdateCASUser
{
    protected $cas;

    public function __construct()
    {
        $this->cas = app('cas');
    }

    public function createOrUpdateCASUser(Request $request)
    {
        $attrs = ['gtGTID', 'email_primary', 'givenName', 'sn'];
        if ($this->cas->isMasquerading()) {
            $masq_attrs = [];
            foreach ($attrs as $attr) {
                $masq_attrs[$attr] = config('cas.cas_masquerade_'.$attr);
            }
            $this->cas->setAttributes($masq_attrs);
        }

        foreach ($attrs as $attr) {
            if (! $this->cas->hasAttribute($attr) || $this->cas->getAttribute($attr) == null) {
                return response(view('errors.generic',
                    ['error_code' => 500,
                        'error_message' => 'Missing/invalid attributes from CAS', ]), 500);
            }
        }

        //User is starting a new session, so let's update data from CAS
        //Sadly we can't use updateOrCreate here because of $guarded in the User model
        $user = User::where('uid', $this->cas->user())->first();
        if ($user == null) {
            $user = new User();
        }
        $user->uid = $this->cas->user();
        $user->gtid = $this->cas->getAttribute('gtGTID');
        $user->gt_email = $this->cas->getAttribute('email_primary');
        $user->first_name = $this->cas->getAttribute('givenName');
        $user->last_name = $this->cas->getAttribute('sn');
        $user->save();

        if ($user->wasRecentlyCreated || $user->roles->count() == 0) {
            $role = Role::where('name', 'non-member')->first();
            if ($role) {
                $user->assignRole($role);
            } else {
                Log::error(get_class()."Role 'non-member' not found for assignment to $user->uid.");
            }
        }

        return $user;
    }
}
