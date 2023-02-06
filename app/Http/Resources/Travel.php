<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Laravel\Nova\Http\Resources\UserResource;

class Travel extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
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
            'tar_required' => $this->tar_required,
            'completion_email_sent' => $this->completion_email_sent,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            // relationships
            'primary_contact' => new UserResource($this->whenLoaded('primaryContact')),
        ];
    }
}
