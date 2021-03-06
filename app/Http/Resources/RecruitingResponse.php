<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Http\Resources\RecruitingVisit as RecruitingVisitResource;
use Illuminate\Http\Resources\Json\JsonResource;

class RecruitingResponse extends JsonResource
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
            // Attributes
            'response' => $this->response,
            'recruiting_visit_id' => $this->recruiting_visit_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            // Relationships
            'recruitingVisit' => new RecruitingVisitResource($this->whenLoaded('recruitingVisit')),
        ];
    }
}
