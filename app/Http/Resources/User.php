<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Http\Resources\Attendance as AttendanceResource;
use App\Http\Resources\DuesTransaction as DuesTransactionResource;
use App\Http\Resources\Event as EventResource;
use App\Http\Resources\Permission as PermissionResource;
use App\Http\Resources\Role as RoleResource;
use App\Http\Resources\Team as TeamResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class User extends JsonResource
{
    public function __construct(
        \App\Models\User|\Illuminate\Http\Resources\MissingValue|null $resource,
        private readonly bool $withManager = false
    ) {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<int|string,mixed>
     */
    public function toArray($request): array
    {
        return [
            // Attributes
            'id' => $this->id,
            'uid' => $this->uid,
            'gtid' => $this->when(Auth::user()->can('read-users-gtid'), $this->gtid),
            'gt_email' => $this->gt_email,
            'email_suppression_reason' => $this->email_suppression_reason,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'preferred_first_name' => $this->preferred_first_name,
            'full_name' => $this->full_name,
            'name' => $this->name,
            'phone' => $this->phone,
            $this->mergeWhen($this->requestingSelf($request) || Auth::user()->can('read-users-emergency_contact'), [
                'emergency_contact_name' => $this->emergency_contact_name,
                'emergency_contact_phone' => $this->emergency_contact_phone,
            ]),
            'join_semester' => $this->join_semester,
            'graduation_semester' => $this->graduation_semester,
            'primary_affiliation' => $this->primary_affiliation,
            'is_student' => $this->is_student,
            'shirt_size' => $this->shirt_size,
            'polo_size' => $this->polo_size,
            $this->mergeWhen($this->requestingSelf($request) || Auth::user()->can('read-users-demographics'), [
                'gender' => $this->gender,
                'ethnicity' => $this->ethnicity,
            ]),
            'resume_date' => $this->resume_date,
            'is_active' => $this->is_active,
            'is_access_active' => $this->is_access_active,
            'signed_latest_agreement' => $this->signed_latest_agreement,
            'github_username' => $this->github_username,
            'github_invite_pending' => $this->github_invite_pending,
            'gmail_address' => $this->gmail_address,
            'clickup_email' => $this->clickup_email,
            'clickup_id' => $this->clickup_id,
            'clickup_invite_pending' => $this->clickup_invite_pending,
            'exists_in_sums' => $this->exists_in_sums,
            $this->mergeWhen($this->requestingSelf($request) || Auth::user()->can('read-dues-transactions'), [
                'has_ordered_polo' => $this->has_ordered_polo,
            ]),
            'manager' => $this->when(
                Auth::user()->can('read-users') && $this->withManager,
                fn (): ?Manager => $this->manager === null ? null : new Manager($this->manager)
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            // Relationships
            'attendance' => AttendanceResource::collection($this->whenLoaded('attendance')),
            'dues' => DuesTransactionResource::collection($this->whenLoaded('dues')),
            'events' => EventResource::collection($this->whenLoaded('events')),
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'teams' => TeamResource::collection($this->whenLoaded('teams')),
            $this->mergeWhen(
                $this->resource->relationLoaded('permissions') && $this->resource->relationLoaded('roles'),
                [
                    'allPermissions' => $this->resource->relationLoaded('permissions') &&
                    $this->resource->relationLoaded('roles') ? $this->getAllPermissions()->pluck('name') : [],
                ]
            ),
            'travel' => $this->when(
                ($this->requestingSelf($request) || Auth::user()->can('manage-travel')) &&
                $this->resource->relationLoaded('assignments'),
                fn (): AnonymousResourceCollection => TravelAssignment::collection($this->assignments)
            ),
        ];
    }

    protected function requestingSelf(Request $request): bool
    {
        return $request->user()->id === $this->id;
    }
}
