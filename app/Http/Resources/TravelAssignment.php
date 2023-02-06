<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Http\Resources\Travel as TravelResource;
use App\Http\Resources\User as UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class TravelAssignment extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string,mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'travel_id' => $this->travel_id,
            'tar_received' => $this->tar_received,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            // relationships
            'travel' => new TravelResource($this->whenLoaded('travel')),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
