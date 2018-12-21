<?php

namespace App\Http\Resources;

use App\Http\Resources\User as UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationTemplate extends JsonResource
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
            'name' => $this->name,
            'subject' => $this->subject,
            'body_markdown' => $this->body_markdown,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            // Relationships
            'creator' => new UserResource($this->whenLoaded('creator')),
        ];
    }
}
