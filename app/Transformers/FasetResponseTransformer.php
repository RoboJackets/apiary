<?php

namespace App\Transformers;

use App\FasetResponse;
use League\Fractal\TransformerAbstract;
use Auth;

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
        $authUser = Auth::user();
        if ($authUser->can('read-faset-visits')) {
            return $this->item($response->fasetSurvey, new FasetSurveyTransformer());
        } else {
            return null;
        }
    }
    
    public function includeVisit(FasetResponse $response)
    {
        $authUser = Auth::user();
        if ($authUser->can('read-faset-visits')) {
            return $this->item($response->fasetVisit, new FasetVisitTransformer());
        } else {
            return null;
        }
    }
}
