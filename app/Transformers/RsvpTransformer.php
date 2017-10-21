<?php

namespace App\Transformers;

use App\Rsvp;
use League\Fractal\TransformerAbstract;

class RsvpTransformer extends TransformerAbstract
{
    /**
     * Allowed related models to include
     * @var array
     */
    protected $availableIncludes = [
        "user",
        "event"
    ];
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Rsvp $rsvp)
    {
        return [
            "id" => $rsvp->id,
            "user_id" => $rsvp->user_id,
            "event_id" => $rsvp->event_id,
            "source" => $rsvp->source,
            "response" => $rsvp->response
        ];
    }

    public function includeUser(Rsvp $rsvp)
    {
        return $this->item($rsvp->user, new UserTransformer());
    }

    public function includeEvent(Rsvp $rsvp)
    {
        return $this->item($rsvp->event, new EventTransformer());
    }
}
