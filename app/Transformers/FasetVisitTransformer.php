<?php

namespace App\Transformers;

use App\FasetVisit;
use League\Fractal\TransformerAbstract;

class FasetVisitTransformer extends TransformerAbstract
{
    /**
     * Allowed related models to include
     * @var array
     */
    protected $availableIncludes = [
        "user",
        "responses",
    ];
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(FasetVisit $visit)
    {
        return [
            "id" => $visit->id,
            "name" => $visit->name,
            "email" => $visit->email,
            "token" => $visit->visit_token,
            "user_id" => $visit->user_id
        ];
    }

    public function includeUser(FasetVisit $visit)
    {
        $authUser = Auth::user();
        if ($authUser->can('read-users')) {
            return $this->item($visit->user, new UserTransformer());
        } else {
            return null;
        }
    }

    public function includeResponses(FasetVisit $visit)
    {
        $authUser = Auth::user();
        if ($authUser->can('read-faset-XXXX')) {
            return $this->collection($visit->fasetResponses, new FasetResponseTransformer());
        } else {
            return null;
        }
    }
}
