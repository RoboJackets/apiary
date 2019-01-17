<?php

namespace App\Http\Resources;

use App\Http\Resources\User as UserResource;
use App\Http\Resources\Event as EventResource;
use Illuminate\Http\Resources\Json\JsonResource;

class Rsvp extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            // Attributes
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_agent' => $this->user_agent,
            'ip_address' => $this->ip_address,
            'event_id' => $this->event_id,
            'source' => $this->source,
            'token' => $this->token,
            'response' => $this->response,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            // Relationships
            'user' => new UserResource($this->whenLoaded('user')),
            'event' => new EventResource($this->whenLoaded('event')),
        ];
    }
}
