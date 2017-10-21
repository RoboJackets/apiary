<?php

namespace App\Transformers;

use App\Team;
use League\Fractal\TransformerAbstract;

class TeamTransformer extends TransformerAbstract
{
    /**
     * Allowed related models to include
     * @var array
     */
    protected $availableIncludes = [
        "users"
    ];
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Team $team)
    {
        return [
            "id" => $team->id,
            "name" => $team->name,
            "description" => $team->description,
            "founding_semester" => $team->founding_semester
        ];
    }

    public function includeUsers(Team $team)
    {
        return $this->collection($team->users, new UserTransformer());
    }
}
