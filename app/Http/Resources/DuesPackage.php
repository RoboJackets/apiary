<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Http\Resources\DuesTransaction as DuesTransactionResource;
use Illuminate\Http\Resources\Json\JsonResource;

class DuesPackage extends JsonResource
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
            'is_active' => $this->is_active,
            'available_for_purchase' => $this->available_for_purchase,
            'effective_start' => $this->effective_start,
            'effective_end' => $this->effective_end,
            'eligible_for_shirt' => $this->eligible_for_shirt,
            'eligible_for_polo' => $this->eligible_for_polo,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            // Relationships
            'transactions' => DuesTransactionResource::collection($this->whenLoaded('transactions')),
        ];
    }
}
