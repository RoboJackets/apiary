<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Http\Resources\Attendance as AttendanceResource;
use App\Http\Resources\DuesTransaction as DuesTransactionResource;
use App\Http\Resources\Event as EventResource;
use App\Http\Resources\Permission as PermissionResource;
use App\Http\Resources\RecruitingVisit as RecruitingVisitResource;
use App\Http\Resources\Role as RoleResource;
use App\Http\Resources\Team as TeamResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array<int|string,mixed>
     */
    public function toArray($request): array
    {
        return [
            // Attributes
            'id' => $this->id,
            'uid' => $this->uid,
            'gtid' => $this->when(Auth::user()->can('read-users-gtid'), $this->gtid),
            'api_token' => $this->when(Auth::user()->can('read-users-api_token'), $this->api_token),
            'slack_id' => $this->slack_id,
            'gt_email' => $this->gt_email,
            'personal_email' => $this->personal_email,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
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
            'shirt_size' => $this->shirt_size,
            'polo_size' => $this->polo_size,
            $this->mergeWhen($this->requestingSelf($request) || Auth::user()->can('read-users-demographics'), [
                'gender' => $this->gender,
                'ethnicity' => $this->ethnicity,
            ]),
            'resume_date' => $this->resume_date,
            'is_active' => $this->is_active,
            'is_access_active' => $this->is_access_active,
            'signed_latest_agreement' => $this->hasSignedLatestAgreement(),
            'github_username' => $this->github_username,
            'github_invite_pending' => $this->github_invite_pending,
            'gmail_address' => $this->gmail_address,
            'clickup_email' => $this->clickup_email,
            'clickup_id' => $this->clickup_id,
            'clickup_invite_pending' => $this->clickup_invite_pending,
            'autodesk_email' => $this->autodesk_email,
            'autodesk_invite_pending' => $this->autodesk_invite_pending,
            'exists_in_sums' => $this->exists_in_sums,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            // Relationships
            'attendance' => AttendanceResource::collection($this->whenLoaded('attendance')),
            'dues' => DuesTransactionResource::collection($this->whenLoaded('dues')),
            'events' => EventResource::collection($this->whenLoaded('events')),
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
            'recruitingVisits' => RecruitingVisitResource::collection($this->whenLoaded('recruitingVisits')),
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'teams' => TeamResource::collection($this->whenLoaded('teams')),
        ];
    }

    protected function requestingSelf(Request $request): bool
    {
        return $request->user()->id === $this->id;
    }
}
