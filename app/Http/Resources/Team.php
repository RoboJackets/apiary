<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Http\Resources\Attendance as AttendanceResource;
use App\Http\Resources\User as UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class Team extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string,mixed>
     */
    public function toArray($request): array
    {
        return [
            // Attributes
            'id' => $this->id,
            'name' => $this->name,
            'self_serviceable' => $this->self_serviceable,
            'visible' => $this->visible,
            'visible_on_kiosk' => $this->visible_on_kiosk,
            'attendable' => $this->attendable,
            'slug' => $this->slug,
            'description' => $this->description,
            'mailing_list_name' => $this->mailing_list_name,
            'slack_channel_id' => $this->slack_channel_id,
            'slack_channel_name' => $this->slack_channel_name,
            'slack_private_channel_id' => $this->slack_private_channel_id,
            'google_group' => $this->google_group,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            // Relationships
            'members' => UserResource::collection($this->whenLoaded('members')),
            'attendance' => AttendanceResource::collection($this->whenLoaded('attendance')),
        ];
    }
}
