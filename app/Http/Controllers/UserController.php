<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Traits\AuthorizeInclude;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;
use App\Http\Requests\StoreUserRequest;
use Illuminate\Database\QueryException;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\User as UserResource;

class UserController extends Controller
{
    use AuthorizeInclude;

    public function __construct()
    {
        $this->middleware('permission:read-users', ['only' => ['index', 'search']]);
        $this->middleware('permission:create-users', ['only' => ['store']]);
        $this->middleware('permission:read-users|read-users-own', ['only' => ['show']]);
        $this->middleware('permission:update-users|update-users-own', ['only' => ['update']]);
        $this->middleware('permission:delete-users', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $include = $request->input('include');
        $users = User::with($this->authorizeInclude(User::class, $include))->get();

        return response()->json(['status' => 'success', 'users' => UserResource::collection($users)]);
    }

    /**
     * Searches for a resource based upon a keyword
     * Accepts GTID, First/Preferred Name, and Username (uid)
     * GTID returns first result, others return all matching (wildcard).
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        if (! $request->filled('keyword')) {
            return response()->json(['status' => 'error', 'error' => 'Missing keyword'], 422);
        }
        $keyword = $request->input('keyword');
        $include = $request->input('include');
        if (is_numeric($keyword)) {
            $results = User::where('gtid', $keyword)->with($this->authorizeInclude(User::class, $include))->get();
        } else {
            $keyword = '%'.$request->input('keyword').'%';
            $results = User::where('uid', 'LIKE', $keyword)
                ->orWhere('first_name', 'LIKE', $keyword)
                ->orWhere('preferred_name', 'LIKE', $keyword)
                ->orWhere('github_username', 'LIKE', $keyword)
                ->with($this->authorizeInclude(User::class, $include))
                ->get();
        }

        return response()->json(['status' => 'success', 'users' => UserResource::collection($results)]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\StoreUserRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = new User();
        if (true === $request->input('generateToken')) {
            $user->api_token = bin2hex(openssl_random_pseudo_bytes(16));
        }

        foreach ($request->rules() as $key => $value) {
            $user->$key = $request->input($key);
        }

        try {
            $user->save();
        } catch (QueryException $e) {
            Bugsnag::notifyException($e);
            $errorMessage = $e->errorInfo[2];

            return response()->json(['status' => 'error', 'message' => $errorMessage], 500);
        }

        if ($request->filled('roles')) {
            foreach ($request->input('roles') as $role) {
                $requestedRole = Role::where('id', $role)->firstOrFail();
                $user->assignRole($requestedRole);
            }
        }

        if (is_numeric($user->id)) {
            $dbUser = User::findOrFail($user->id)->makeVisible('api_token');

            return response()->json(['status' => 'success', 'user' => $dbUser], 201);
        }

        return response()->json(['status' => 'error', 'message' => 'Unknown error.'], 500);
    }

    /**
     * Display the specified resource.
     *
     * @param string $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $include = $request->input('include');
        $user = User::findByIdentifier($id)->with($this->authorizeInclude(User::class, $include))->first();
        if ($user) {
            $requestingUser = $request->user();
            //Enforce users only viewing themselves (read-users-own)
            if ($requestingUser->cant('read-users') && $requestingUser->id !== $user->id) {
                return response()->json(['status' => 'error',
                    'message' => 'Forbidden - You do not have permission to view this User.',
                ], 403);
            }

            //Show API tokens only to admins and the users themselves
            if ($requestingUser->id === $user->id || $requestingUser->hasRole('admin')) {
                $user = $user->makeVisible('api_token');
            }

            return response()->json(['status' => 'success', 'user' => new UserResource($user)]);
        }

        return response()->json(['status' => 'error', 'message' => 'User not found.'], 404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param string $id
     * @param \App\Http\Requests\UpdateUserRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(string $id, UpdateUserRequest $request): JsonResponse
    {
        $requestingUser = $request->user();
        $user = User::findByIdentifier($id)->first();
        if (! $user) {
            return response()->json(['status' => 'error', 'message' => 'User not found.'], 404);
        }

        //Enforce users only updating themselves (update-users-own)
        if ($requestingUser->cant('update-users') && $requestingUser->id !== $user->id) {
            return response()->json(['status' => 'error',
                'message' => 'Forbidden - You do not have permission to update this User.',
            ], 403);
        }

        //Update only included fields
        $validatedFields = $request->validated();

        //Generate an API token for the user if requested *AND* the requesting user is self or admin
        //This is deliberately doing a separate update/save of the user model because `api_token` MUST
        //be prevented from mass assignment, otherwise weird things will happen when you `PUT` a User
        //while authenticating with an API token.
        if (null !== $request->input('generateToken')
            && ($requestingUser->hasRole('admin') || $requestingUser->id === $user->id)
        ) {
            $user->api_token = bin2hex(openssl_random_pseudo_bytes(16));
            $user->save();
        }
        unset($request['generateToken']);

        $user->update($validatedFields);

        if ($request->filled('roles')) {
            $user->roles()->sync($request->input('roles'));
        }

        $user = User::find($user->id);

        //Show API tokens only to admins and the users themselves
        if ($requestingUser->id === $user->id || $requestingUser->hasRole('admin')) {
            $user = $user->makeVisible('api_token');
        }

        if ($user) {
            return response()->json(['status' => 'success', 'user' => new UserResource($user)]);
        }

        return response()->json(['status' => 'error', 'message' => 'Unknown error.'], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $user = User::findByIdentifier($id)->first();
        if ($user->delete()) {
            return response()->json(['status' => 'success', 'message' => 'User deleted.']);
        }

        return response()->json(
            [
                'status' => 'error',
                'message' => 'User does not exist or was previously deleted.',
            ],
            422
        );
    }
}
