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
        return $this->item($visit->user, new UserTransformer());
    }

    public function includeResponses(FasetVisit $visit)
    {
        return $this->collection($visit->fasetResponses, new FasetResponseTransformer());
    }
}
