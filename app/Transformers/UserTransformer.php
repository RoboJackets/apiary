<?php

namespace App\Transformers;

use Auth;
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

    public function includeDemographics(User $user)
    {
        $authUser = Auth::user();
        if ($authUser->can('read-users-demographics')) {
            return $this->item($user, new UserDemographicsTransformer());
        } else {
            return null;
        }
    }
    
    public function includeEmergencyContact(User $user)
    {
        $authUser = Auth::user();
        if ($authUser->can('read-users-emergency-contact')) {
            return $this->item($user, new EmergencyContactTransformer());
        } else {
            return null;
        }
    }

    public function includePermissions(User $user)
    {
        $authUser = Auth::user();
        if ($authUser->can('read-permissions')) {
            return $this->collection($user->permissions, new PermissionTransformer());
        } else {
            return null;
        }
    }
    
    public function includeRoles(User $user)
    {
        $authUser = Auth::user();
        if ($authUser->can('read-roles')) {
            return $this->collection($user->roles, new RoleTransformer());
        } else {
            return null;
        }
    }
    
    public function includeRvsps(User $user)
    {
        $authUser = Auth::user();
        if ($authUser->can('read-rsvps')) {
            return $this->collection($user->rsvps, new RsvpTransformer());
        } else {
            return null;
        }
    }
    
    public function includeEvents(User $user)
    {
        $authUser = Auth::user();
        if ($authUser->can('read-events')) {
            return $this->collection($user->events, new RsvpTransformer());
        } else {
            return null;
        }
    }
    
    public function includeDues(User $user)
    {
        $authUser = Auth::user();
        if ($authUser->can('read-dues')) {
            return $this->collection($user->dues, new DuesTransactionTransformer());
        } else {
            return null;
        }
    }
    
    public function includeTeams(User $user)
    {
        $authUser = Auth::user();
        if ($authUser->can('read-teams')) {
            return $this->collection($user->teams, new TeamTransformer());
        } else {
            return null;
        }
    }
    
    public function includeFasetVisits(User $user)
    {
        $authUser = Auth::user();
        if ($authUser->can('read-faset-visits')) {
            return $this->collection($user->fasetVisits, new FasetVisitTransformer());
        } else {
            return null;
        }
    }
}
