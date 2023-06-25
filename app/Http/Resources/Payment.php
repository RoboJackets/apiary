<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Http\Resources\DuesTransaction as DuesTransactionResource;
use App\Http\Resources\TravelAssignment as TravelAssignmentResource;
use App\Http\Resources\User as UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class Payment extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string,mixed>
     */
    public function toArray(Request $request): array
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
            'card_type' => $this->card_type,
            'card_brand' => $this->card_brand,
            'last_4' => $this->last_4,
            'entry_method' => $this->entry_method,
            'statement_description' => $this->statement_description,
            'receipt_url' => $this->receipt_url,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            // relationships
            'recorded_by_user' => Auth::user()->can('read-users') ?
                new UserResource($this->whenLoaded('recordedBy')) :
                $this->when($this->recordedBy, [
                    'name' => $this->recordedBy?->name,
                ]),
            'dues_transaction' => new DuesTransactionResource($this->whenLoaded('duesTransaction')),
            'travel_assignment' => new TravelAssignmentResource($this->whenLoaded('travelAssignment')),
        ];
    }
}
