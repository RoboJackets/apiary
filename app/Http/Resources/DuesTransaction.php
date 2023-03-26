<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Http\Resources\DuesPackage as DuesPackageResource;
use App\Http\Resources\Payment as PaymentResource;
use App\Http\Resources\User as UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DuesTransaction extends JsonResource
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
            'user_id' => $this->user_id,
            'dues_package_id' => $this->dues_package_id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            // Relationships
            'user' => new UserResource($this->whenLoaded('user')),
            'package' => new DuesPackageResource($this->whenLoaded('package')),
            'payment' => PaymentResource::collection($this->whenLoaded('payment')),
            'merchandise' => Merchandise::collection($this->whenLoaded('merchandise')),
        ];
    }
}
