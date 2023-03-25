<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TravelAssignment extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, int|JsonResource>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'travel' => Travel::make($this->travel),
        ];
    }
}
