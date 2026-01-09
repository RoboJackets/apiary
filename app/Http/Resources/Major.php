<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Major extends JsonResource
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
            'display_name' => $this->display_name,
            'gtad_majorgroup_name' => $this->gtad_majorgroup_name,
            'whitepages_ou' => $this->whitepages_ou,
            'school' => $this->school,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
