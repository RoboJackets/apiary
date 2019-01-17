<?php

namespace App\Http\Resources;

use Auth;
use App\Http\Resources\Team as TeamResource;
use App\Http\Resources\Event as EventResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\DuesTransaction as DuesTransactionResource;
use App\Http\Resources\RecruitingVisit as RecruitingVisitResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            // Attributes
            'id' => $this->id,
            'uid' => $this->uid,
            'gtid' => $this->when(Auth::user()->can('read-users-gtid'), $this->gtid),
            'api_token' => $this->when(Auth::user()->can('read-users-api_token'), $this->api_token),
            'slack_id' => $this->slack_id,
            'gt_email' => $this->gt_email,
            'personal_email' => $this->personal_email,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'preferred_first_name' => $this->preferred_first_name,
            'full_name' => $this->full_name,
            'name' => $this->name,
            'phone' => $this->phone,
            $this->mergeWhen($this->requestingSelf($request) || Auth::user()->can('read-users-emergency_contact'), [
                'emergency_contact_name' => $this->emergency_contact_name,
                'emergency_contact_phone' => $this->emergency_contact_phone,
            ]),
            'join_semester' => $this->join_semester,
            'graduation_semester' => $this->graduation_semester,
            'shirt_size' => $this->shirt_size,
            'polo_size' => $this->polo_size,
            $this->mergeWhen($this->requestingSelf($request) || Auth::user()->can('read-users-demographics'), [
                'gender' => $this->gender,
                'ethnicity' => $this->ethnicity,
            ]),
            'accept_safety_agreement' => $this->accept_safety_agreement,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            // Relationships
            'dues' => DuesTransactionResource::collection($this->whenLoaded('dues')),
            'events' => EventResource::collection($this->whenLoaded('events')),
            'recruitingVisits' => RecruitingVisitResource::collection($this->whenLoaded('recruitingVisits')),
            'teams' => TeamResource::collection($this->whenLoaded('teams')),
        ];
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    protected function requestingSelf($request)
    {
        return $request->user()->id === $this->id;
    }
}
