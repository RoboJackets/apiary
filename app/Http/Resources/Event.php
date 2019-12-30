<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Http\Resources\Rsvp as RsvpResource;
use App\Http\Resources\User as UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class Event extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string,mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'cost' => $this->cost,
            'allow_anonymous_rsvp' => (bool) $this->allow_anonymous_rsvp,
            'location' => $this->location,
            'organizer_name' => $this->organizer_name,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'organizer' => new UserResource($this->whenLoaded('organizer')),
            'rsvps' => new RsvpResource($this->whenLoaded('rsvps')),
        ];
    }
}
