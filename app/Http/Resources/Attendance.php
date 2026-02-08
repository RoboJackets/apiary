<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Http\Resources\User as UserResource;
use App\Util\UserOrClient;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Attendance extends JsonResource
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
            // Attributes
            'id' => $this->id,
            'attendable_type' => $this->attendable_type,
            'attendable_id' => $this->attendable_id,
            'attendable' => $this->attendable_type === \App\Models\Team::getMorphClassStatic() ?
                    new Team($this->whenLoaded('attendable')) :
                    new Event($this->whenLoaded('attendable')),
            'gtid' => $this->when(
                UserOrClient::can('read-users-gtid'),
                $this->gtid
            ),
            'source' => $this->source,
            'recorded_by' => new Manager($this->whenLoaded('recorded')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            // Relationships
            'attendee' => new UserResource($this->whenLoaded('attendee')),
            // This deliberately doesn't include the remote attendance link as there is no HTTP resource for it
        ];
    }
}
