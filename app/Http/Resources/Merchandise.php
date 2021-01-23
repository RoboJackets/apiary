<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Merchandise extends JsonResource
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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            // Instead of defining a resource for the pivot model, this will contain the group from the pivot, or null
            // if there is not the correct pivot or no pivot at all.
            'group' => optional($this->pivot)->group,

            // Relationships
            'dues_transactions' => DuesTransaction::collection($this->whenLoaded('transactions')),
            'dues_packages' => DuesTransaction::collection($this->whenLoaded('packages')),
        ];
    }
}
