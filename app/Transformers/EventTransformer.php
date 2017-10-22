<?php

namespace App\Transformers;

use App\Event;
use League\Fractal\TransformerAbstract;

class EventTransformer extends TransformerAbstract
{
    /**
     * Allowed related models to include
     * @var array
     */
    protected $availableIncludes = [
        "organizer"
    ];
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Event $event)
    {
        return [
            "id" => $event->id,
            "name" => $event->name,
            "price" => $event->price,
            "allow_anonymous_rsvp" => $event->allow_anonymous_rsvp,
            "organizer_id" => $event->organizer_id,
            "location" => $event->location,
            "start_time" => $event->start_time,
            "end_time" => $event->end_time
        ];
    }

    public function includeOrganizer(Event $event)
    {
        $authUser = Auth::user();
        if ($authUser->can('read-users')) {
            return $this->item($event->organizer, new UserTransformer());
        } else {
            return null;
        }
        
    }
}
