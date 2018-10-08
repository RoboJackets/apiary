<?php

namespace App\Http\Resources;

use App\Http\Resources\User as UserResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Payment as PaymentResource;
use App\Http\Resources\DuesPackage as DuesPackageResource;

class DuesTransaction extends JsonResource
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
            'id' => $this->id,
            'user_id' => $this->user_id,
            'dues_package_id' => $this->dues_package_id,
            'status' => $this->status,
            'swag_shirt_provided' => $this->swag_shirt_provided,
            'swag_shirt_providedBy' => $this->swag_shirt_providedBy,
            'swag_shirt_status' => $this->swag_shirt_status,
            'swag_polo_provided' => $this->swag_polo_provided,
            'swag_polo_providedBy' => $this->swag_polo_providedBy,
            'swag_polo_status' => $this->swag_polo_status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            // Relationships
            'user' => new UserResource($this->whenLoaded('user')),
            'package' => new DuesPackageResource($this->whenLoaded('package')),
            'payment' => PaymentResource::collection($this->whenLoaded('payment')),
        ];
    }
}
