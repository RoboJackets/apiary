<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActionEvent extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'actor' => new Manager($this->user),
            'name' => $this->name,
            'actionable_type' => $this->actionable_type,
            'actionable_id' => $this->actionable_id,
            ...$this->getRelationshipDetails('actionable'),
            'target_type' => $this->target_type,
            'target_id' => $this->target_id,
            ...$this->getRelationshipDetails('target'),
            'fields' => $this->fields,
            'exception' => $this->exception,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }

    private function getRelationshipDetails(string $relationshipType): array
    {
        $model_type = $this->{$relationshipType.'_type'};

        return match ($model_type) {
            'App\AttendanceExport' => [],
            'App\NotificationTemplate' => [],
            'App\RecruitingVisit' => [],
            'Illuminate\Database\Eloquent\Relations\Pivot' => [],
            'Illuminate\Database\Eloquent\Relations\MorphPivot' => [],
            \App\Models\Airport::class => [
                $relationshipType => $this->{$relationshipType},
            ],
            \App\Models\ClassStanding::class => [
                $relationshipType => $this->{$relationshipType},
            ],
            \App\Models\DocuSignEnvelope::class => [
                $relationshipType => $this->{$relationshipType},
            ],
            \App\Models\FiscalYear::class => [
                $relationshipType => $this->{$relationshipType},
            ],
            \App\Models\MembershipAgreementTemplate::class => [
                $relationshipType => $this->{$relationshipType},
            ],
            \Laravel\Passport\Client::class => [
                $relationshipType => $this->{$relationshipType},
            ],
            \App\Models\OAuth2Client::class => [
                $relationshipType => $this->{$relationshipType},
            ],
            \App\Models\RemoteAttendanceLink::class => [
                $relationshipType => $this->{$relationshipType},
            ],
            \App\Models\Rsvp::class => [
                $relationshipType => $this->{$relationshipType},
            ],
            \App\Models\Signature::class => [
                $relationshipType => $this->{$relationshipType},
            ],
            \App\Models\Signature::getMorphClassStatic() => [
                $relationshipType => $this->{$relationshipType},
            ],
            \App\Models\Sponsor::class => [
                $relationshipType => $this->{$relationshipType},
            ],
            \App\Models\SponsorDomain::class => [
                $relationshipType => $this->{$relationshipType},
            ],
            \Spatie\Permission\Models\Permission::class => [
                $relationshipType => $this->{$relationshipType},
            ],
            \Spatie\Permission\Models\Role::class => [
                $relationshipType => $this->{$relationshipType},
            ],
            \App\Models\Attendance::class => [
                $relationshipType => new Attendance($this->whenLoaded($relationshipType)),
            ],
            \App\Models\DuesPackage::class => [
                $relationshipType => new DuesPackage($this->whenLoaded($relationshipType)),
            ],
            \App\Models\DuesTransaction::class => [
                $relationshipType => new DuesTransaction($this->whenLoaded($relationshipType)),
            ],
            \App\Models\DuesTransaction::getMorphClassStatic() => [
                $relationshipType => new DuesTransaction($this->whenLoaded($relationshipType)),
            ],
            \App\Models\DuesTransactionMerchandise::class => [
                $relationshipType => new DuesTransactionMerchandise($this->whenLoaded($relationshipType)),
            ],
            \App\Models\Event::class => [
                $relationshipType => new Event($this->whenLoaded($relationshipType)),
            ],
            \App\Models\Event::getMorphClassStatic() => [
                $relationshipType => new Event($this->whenLoaded($relationshipType)),
            ],
            \App\Models\Major::class => [
                $relationshipType => new Major($this->whenLoaded($relationshipType)),
            ],
            \App\Models\Merchandise::class => [
                $relationshipType => new Merchandise($this->whenLoaded($relationshipType)),
            ],
            \App\Models\Payment::class => [
                $relationshipType => new Payment($this->whenLoaded($relationshipType)),
            ],
            \App\Models\Team::class => [
                $relationshipType => new Team($this->whenLoaded($relationshipType)),
            ],
            \App\Models\Team::getMorphClassStatic() => [
                $relationshipType => new Team($this->whenLoaded($relationshipType)),
            ],
            \App\Models\Travel::class => [
                $relationshipType => new Travel($this->whenLoaded($relationshipType)),
            ],
            \App\Models\User::class => [
                $relationshipType => new Manager($this->whenLoaded($relationshipType)),
            ],
            \App\Models\TravelAssignment::getMorphClassStatic() => [
                $relationshipType => new TravelAssignment($this->whenLoaded($relationshipType)),
            ],
        };
    }
}
