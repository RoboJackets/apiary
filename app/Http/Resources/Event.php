<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Http\Resources\Rsvp as RsvpResource;
use App\Http\Resources\User as UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Event extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string,mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'allow_anonymous_rsvp' => (bool) $this->allow_anonymous_rsvp,
            'location' => $this->location,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'organizer' => new UserResource($this->whenLoaded('organizer')),
            'rsvps' => RsvpResource::collection($this->whenLoaded('rsvps')),
        ];
    }
}
