<?php declare(strict_types = 1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter,SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint

namespace App\Http\Resources;

use App\Http\Resources\User as UserResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class RecruitingVisit extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return array<string,mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // Attributes
            'id' => $this->id,
            'recruiting_name' => $this->recruiting_name,
            'recruiting_email' => $this->recruiting_email,
            'visit_token' => $this->visit_token,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            // Relationships
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
