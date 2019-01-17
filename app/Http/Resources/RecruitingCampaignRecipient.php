<?php

namespace App\Http\Resources;

use App\Http\Resources\User as UserResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\RecruitingVisit as RecruitingVisitResource;
use App\Http\Resources\RecruitingCampaign as RecruitingCampaignResource;

class RecruitingCampaignRecipient extends JsonResource
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
            'email_address' => $this->email_address,
            'source' => $this->source,
            'recruiting_campaign_id' => $this->recruiting_campaign_id,
            'recruiting_visit_id' => $this->recruiting_visit_id,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            // Relationships
            'recruitingVisit' => new RecruitingVisitResource($this->whenLoaded('recruitingVisit')),
            'recruitingCampaign' => new RecruitingCampaignResource($this->whenLoaded('recruitingCampaign')),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
