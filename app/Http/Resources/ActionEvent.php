<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActionEvent extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'actor' => [
                'id' => $this->user->id,
                'uid' => $this->user->uid,
                'name' => $this->user->full_name,
                'gtPersonDirectoryId' => $this->user->gtDirGUID,
            ],
            'name' => $this->name,
            'actionable_type' => $this->actionable_type,
            'actionable_id' => $this->actionable_id,
            'target_type' => $this->target_type,
            'target_id' => $this->target_id,
            'model_type' => $this->model_type,
            'model_id' => $this->model_id,
            'fields' => $this->fields,
            'exception' => $this->exception,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
