<?php

namespace App\Transformers;

use App\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    /**
     * Allowed related models to include
     * @var array
     */
    protected $availableIncludes = [
        'fasetvisits',
        'teams',
        'dues',
        'events',
        'rsvps',
        'roles',
        'permissions',
        'emergency_contact',
        'demographics'
    ];
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(User $user)
    {
        return [
            "id" => (int) $user->id,
            "uid" => $user->uid,
            "gt_email" => $user->gt_email,
            "personal_email" => $user->personal_email,
            "first_name" => $user->first_name,
            "middle_name" => $user->middle_name,
            "last_name" => $user->last_name,
            "preferred_name" => $user->preferred_name,
            "phone" => $user->phone,
            "join_semester" => $user->join_semester,
            "graduation_semester" => $user->graduation_semester,
            "shirt_size" => $user->shirt_size,
            "polo_size" => $user->polo_size,
        ];
    }

    public function includeRoles(User $user)
    {
        return $this->collection($user->roles, new RoleTransformer());
    }

    public function includeDemographics(User $user)
    {
        return $this->item($user, new UserDemographicsTransformer());
    }
    
    public function includeEmergencyContact(User $user)
    {
        if ($user->hasRole('member')) {
            return $this->item($user, new EmergencyContactTransformer());
        } else {
            return null;
        }
    }
}
