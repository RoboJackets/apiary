<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TravelAssignment extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, int|JsonResource>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'travel' => Travel::make($this->travel),
        ];
    }
}
