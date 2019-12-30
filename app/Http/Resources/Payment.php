<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter,SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint,SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Payment extends JsonResource
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
            'id' => $this->id,
            'payable_id' => $this->payable_id,
            'payable_type' => $this->payable_type,
            'amount' => $this->amount,
            'processing_fee' => $this->processing_fee,
            'method' => $this->method,
            'method_presentation' => $this->method_presentation,
            'recorded_by' => $this->recorded_by,
            'checkout_id' => $this->checkout_id,
            'client_txn_id' => $this->client_txn_id,
            'server_txn_id' => $this->server_txn_id,
            'unique_id' => $this->unique_id,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
