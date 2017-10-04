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
        'permissions'
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
            "name" => $user->name,
        ];
    }

    public function includeFasetVisits(User $user)
    {
        return $this->collection($user->fasetVisits, null);
    }
}
