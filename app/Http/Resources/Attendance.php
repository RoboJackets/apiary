<?php

namespace App\Http\Resources;

use App\Http\Resources\User as UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class Attendance extends JsonResource
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
            'attendable_type' => $this->attendable_type,
            'attendable_id' => $this->attendable_id,
            'gtid' => $this->when(Auth::user()->can('read-users-gtid'), $this->gtid),
            'source' => $this->source,
            'recorded_by' => $this->recorded_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            // Relationships
            'attendee' => new UserResource($this->whenLoaded('attendee')),
        ];
    }
}
