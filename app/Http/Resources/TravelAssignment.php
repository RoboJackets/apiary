<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Http\Resources\User as UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TravelAssignment extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, int|JsonResource>
     */
    public function toArray(Request $request): array
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
            'travel' => Travel::make($this->travel),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
