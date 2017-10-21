<?php

namespace App\Transformers;

use App\FasetResponse;
use League\Fractal\TransformerAbstract;

class FasetResponseTransformer extends TransformerAbstract
{
    /**
     * Allowed related models to include
     * @var array
     */
    protected $availableIncludes = [
        "survey",
        "visit"
    ];
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(FasetResponse $response)
    {
        return [
            "response" => $response->response,
            "faset_survey_id" => $response->faset_survey_id,
            "faset_visit_id" => $response->faset_visit_id,
        ];
    }

    public function includeSurvey(FasetResponse $response)
    {
        return $this->item($response->fasetSurvey(), new FasetSurveyTransformer());
    }
    
    public function includeVisit(FasetResponse $response)
    {
        return $this->item($response->fasetVisit(), new FasetVisitTransformer());
    }
}
