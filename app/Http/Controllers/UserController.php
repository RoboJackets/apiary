<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.RequireSingleLineCondition

namespace App\Http\Controllers;

use App\Http\Requests\SearchByEmailRequest;
use App\Http\Requests\SelfServiceAccessOverrideRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\Manager;
use App\Http\Resources\User as UserResource;
use App\Jobs\PushToJedi;
use App\Models\User;
use App\Util\AuthorizeInclude;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class UserController implements HasMiddleware
{
    #[\Override]
    public static function middleware(): array
    {
        return [
            new Middleware('permission:read-users', only: ['index', 'indexManagers', 'search', 'searchByEmail']),
            new Middleware('permission:create-users', only: ['store']),
            new Middleware('permission:read-users|read-users-own', only: ['show']),
            new Middleware('permission:update-users|update-users-own', only: ['update', 'applySelfOverride']),
            new Middleware('permission:delete-users', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $include = $request->input('include');
        $users = User::with(AuthorizeInclude::authorize(User::class, $include))->get();

        return response()->json(['status' => 'success', 'users' => UserResource::collection($users)]);
    }

    public function indexManagers(Request $request): JsonResponse
    {
        return response()
            ->json(
                [
                    'status' => 'success',
                    'users' => Manager::collection(
                        User::where(static function (Builder $query): void {
                            $query->whereHas('manages')
                                ->orWhereHas('roles', static function (Builder $query): void {
                                    $query->whereIn('name', ['project-manager', 'officer']);
                                });
                        })
                            ->whereHas('classStanding')
                            ->whereHas('majors')
                            ->whereHas('attendance')
                            ->whereHas('teams')
                            ->whereHas('paidDues')
                            ->accessActive()
                            ->whereDoesntHave('duesPackages', static function (Builder $query): void {
                                $query->where('restricted_to_students', false);
                            })
                            ->where('primary_affiliation', '=', 'student')
                            ->where('has_ever_logged_in', '=', true)
                            ->where('is_service_account', '=', false)
                            ->get()
                    ),
                ]
            );
    }

    /**
     * Searches for a resource based upon a keyword
     * Accepts GTID, First/Preferred Name, and Username (uid)
     * GTID returns first result, others return all matching (wildcard).
     */
    public function search(Request $request): JsonResponse
    {
        if (! $request->filled('keyword')) {
            return response()->json(['status' => 'error', 'error' => 'Missing keyword'], 422);
        }
        $keyword = $request->input('keyword');
        $include = $request->input('include');
        if (is_numeric($keyword)) {
            $results = User::where('gtid', $keyword)->with(AuthorizeInclude::authorize(User::class, $include))->get();
        } else {
            $keyword = '%'.$request->input('keyword').'%';
            $results = User::where('uid', 'LIKE', $keyword)
                ->orWhere('first_name', 'LIKE', $keyword)
                ->orWhere('preferred_name', 'LIKE', $keyword)
                ->orWhere('github_username', 'LIKE', $keyword)
                ->with(AuthorizeInclude::authorize(User::class, $include))
                ->get();
        }

        return response()->json(['status' => 'success', 'users' => UserResource::collection($results)]);
    }

    public function searchByEmail(SearchByEmailRequest $request): JsonResponse
    {
        return response()->json(
            [
                'status' => 'success',
                'user' => new UserResource(
                    User::where('gt_email', '=', $request->input('email'))
                        ->orWhere('gmail_address', '=', $request->input('email'))
                        ->orWhere('clickup_email', '=', $request->input('email'))
                        ->sole(),
                    withManager: false
                ),
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = new User();

        foreach (array_keys($request->rules()) as $key) {
            $user->$key = $request->input($key);
        }

        $user->save();

        if ($request->filled('roles')) {
            foreach ($request->input('roles') as $role) {
                $requestedRole = Role::where('id', $role)->firstOrFail();
                $user->assignRole($requestedRole);
            }
        }

        $dbUser = User::findOrFail($user->id);

        return response()->json(['status' => 'success', 'user' => $dbUser], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $include = $request->input('include');
        $user = User::findByIdentifier($id)->with(AuthorizeInclude::authorize(User::class, $include))->first();
        if ($user !== null) {
            $requestingUser = $request->user();
            // Enforce users only viewing themselves (read-users-own)
            if ($requestingUser->cant('read-users') && $requestingUser->id !== $user->id) {
                return response()->json(['status' => 'error',
                    'message' => 'Forbidden - You do not have permission to view this User.',
                ], 403);
            }

            return response()->json(['status' => 'success', 'user' => new UserResource($user, true)]);
        }

        return response()->json(['status' => 'error', 'message' => 'User not found.'], 404);
    }

    /**
     * Display the resource for one's self.
     */
    public function showSelf(Request $request): JsonResponse
    {
        $include = $request->input('include');
        $id = $request->user()->id;
        $allowedIncludes = AuthorizeInclude::authorize(User::class, $include);
        $allowedIncludes[] = 'permissions';
        $allowedIncludes[] = 'roles';
        $user = User::findByIdentifier($id)->with($allowedIncludes)->first();

        if ($user === null) {
            // This shouldn't be possible.
            return response()->json(['status' => 'error', 'message' => 'User not found.'], 404);
        }

        return response()->json(['status' => 'success', 'user' => new UserResource($user)]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(string $id, UpdateUserRequest $request): JsonResponse
    {
        $requestingUser = $request->user();
        $user = User::findByIdentifier($id)->first();
        if ($user === null) {
            return response()->json(['status' => 'error', 'message' => 'User not found.'], 404);
        }

        // Enforce users only updating themselves (update-users-own)
        if ($requestingUser->cant('update-users') && $requestingUser->id !== $user->id) {
            return response()->json(['status' => 'error',
                'message' => 'Forbidden - You do not have permission to update this User.',
            ], 403);
        }

        // Update only included fields
        $validatedFields = $request->validated();

        if ($request->filled('clickup_email')) {
            // Check that this is one of their verified emails
            // gmail_address can be null and clickup_email can't be empty here so fall back to an empty string.
            if (! in_array(
                $request->input('clickup_email'),
                [
                    strtolower($user->uid).'@gatech.edu',
                    strtolower($user->gt_email),
                    strtolower($user->gmail_address ?? ''),
                ],
                true
            )) {
                return response()->json(['status' => 'error',
                    'message' => 'requested clickup_email value has not been verified',
                ], 422);
            }
        }

        if ($request->filled('preferred_first_name')) {
            // This uses a setter and is not the same as the database column, so set it manually.
            $user->preferred_first_name = $validatedFields['preferred_first_name'];
        }

        $user->update($validatedFields);

        if ($request->filled('roles')) {
            $user->roles()->sync($request->input('roles'));
        }

        $user = User::find($user->id);

        if ($user !== null) {
            return response()->json(['status' => 'success', 'user' => new UserResource($user)]);
        }

        return response()->json(['status' => 'error', 'message' => 'Unknown error.'], 500);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $user = User::findByIdentifier($id)->first();
        if ($user === null) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'User does not exist or was previously deleted.',
                ],
                422
            );
        }

        if ($user->delete() === true) {
            return response()->json(['status' => 'success', 'message' => 'User deleted.']);
        }

        return response()->json(
            [
                'status' => 'error',
            ],
            500
        );
    }

    public function showProfile(Request $request)
    {
        return view('users/userprofile', ['id' => $request->user()->id]);
    }

    public function applySelfOverride(SelfServiceAccessOverrideRequest $request): JsonResponse
    {
        $requestingUser = $request->user();

        $overrideEligibility = $requestingUser->self_service_override_eligibility;
        $overrideEndDate = $overrideEligibility->override_until;

        if ($overrideEligibility->eligible && ! $request->boolean('preview')) {
            Log::info('Applying self-service access override for '.$requestingUser->uid.' until '.$overrideEndDate);
            $requestingUser->access_override_until = $overrideEndDate;
            $requestingUser->access_override_by_id = $request->user()->id;
            $requestingUser->save();
        }

        PushToJedi::dispatch($requestingUser, $requestingUser::class, $requestingUser->id, 'self_service_override');

        return response()->json([
            'status' => 'success',
            'preview' => $request->boolean('preview'),
            'eligible' => $overrideEligibility->eligible,
            'reason' => $overrideEligibility->ineligible_reason,
            'user_rectifiable' => $overrideEligibility->user_rectifiable,
            'conditions' => $overrideEligibility->required_conditions,
            'tasks' => $overrideEligibility->required_tasks,
            'override_until' => $overrideEndDate,
        ]);
    }
}
