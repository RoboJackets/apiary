<?php

namespace App\Transformers;

use App\FasetSurvey;
use League\Fractal\TransformerAbstract;

class FasetSurveyTransformer extends TransformerAbstract
{
    /**
     * Allowed related models to include
     * @var array
     */
    protected $availableIncludes = [
        "responses"
    ];
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(FasetSurvey $survey)
    {
        return [
            "id" => $survey->id,
            "question" => $survey->question
        ];
    }

    public function includeResponses(FasetSurvey $survey)
    {
        $authUser = Auth::user();
        if ($authUser->can('read-faset-XXXX')) {
            return $this->collection($survey->fasetResponses, new FasetResponseTransformer());
        } else {
            return null;
        }
    }
}
