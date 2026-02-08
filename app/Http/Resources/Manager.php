<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Manager extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, int|string>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'uid' => $this->uid,
            'gtPersonDirectoryId' => $this->gtDirGUID,
            'is_service_account' => $this->is_service_account,
        ];
    }
}
