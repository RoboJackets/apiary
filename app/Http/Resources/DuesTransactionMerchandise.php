<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;

class DuesTransactionMerchandise extends JsonResource
{
    public function __construct(
        \App\Models\DuesTransactionMerchandise|MissingValue|null $resource,
        private readonly bool $withMerchandise = true
    ) {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'provided_at' => $this->provided_at,
            'provided_by' => Manager::make($this->providedBy),
            'provided_via' => $this->provided_via,
            'size' => [
                'short' => $this->size,
                'display_name' => $this->size === null ? null : \App\Models\User::$shirt_sizes[$this->size],
            ],
            'merchandise' => $this->when($this->withMerchandise, $this->merchandise),
        ];
    }
}
