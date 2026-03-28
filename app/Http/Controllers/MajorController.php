<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\Major as MajorResource;
use App\Models\Major;
use Illuminate\Http\JsonResponse;

class MajorController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'majors' => MajorResource::collection(Major::all()),
        ]);
    }

    /**
     * Returns a list of majors (display names only) for active users who have uploaded resumes.
     */
    public function getResumeMajors()
    {
        $majors = User::selectRaw('distinct(majors.display_name) as distinct_display_names')
            ->active()
            ->whereNotNull('resume_date')
            ->where('primary_affiliation', 'student')
            ->where('is_service_account', '=', false)
            ->whereDoesntHave('duesPackages', static function (Builder $q): void {
                $q->where('restricted_to_students', false);
            })
            ->leftJoin('major_user', static function (JoinClause $join): void {
                $join->on('users.id', '=', 'major_user.user_id')
                    ->whereNull('major_user.deleted_at');
            })
            ->leftJoin(
                'majors',
                'major_user.major_id',
                '=',
                'majors.id'
            )
            ->orderBy('distinct_display_names')
            ->pluck('distinct_display_names')
            ->mapWithKeys(
                static fn (?string $name): array => $name === null ? [] : [$name => $name]
            )
            ->toArray();
        
        return response()->json([
            'status' => 'success',
            'majors' => $majors,
        ]);
    }
}
