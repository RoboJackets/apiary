<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Http\Resources\DuesTransaction as DuesTransactionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DuesPackage extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string,mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'cost' => $this->cost,
            'is_active' => $this->is_active,
            'available_for_purchase' => $this->available_for_purchase,
            'restricted_to_students' => $this->restricted_to_students,
            'effective_start' => $this->effective_start,
            'effective_end' => $this->effective_end,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            // Relationships
            'transactions' => DuesTransactionResource::collection($this->whenLoaded('transactions')),
            'merchandise' => Merchandise::collection($this->whenLoaded('merchandise')),
        ];
    }
}
