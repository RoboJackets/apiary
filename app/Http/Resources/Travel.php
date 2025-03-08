<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Laravel\Nova\Http\Resources\UserResource;

class Travel extends JsonResource
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
            'id' => $this->id,
            'name' => $this->name,
            'destination' => $this->destination,
            'primary_contact_user_id' => $this->primary_contact_user_id,
            'departure_date' => $this->departure_date,
            'return_date' => $this->return_date,
            'fee_amount' => $this->fee_amount,
            'included_with_fee' => $this->included_with_fee,
            'not_included_with_fee' => $this->not_included_with_fee,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            // relationships
            'primary_contact' => new UserResource($this->whenLoaded('primaryContact')),
        ];
    }
}
