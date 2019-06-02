<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Traits\AuthorizeInclude;
use Spatie\Permission\Models\Role;
use Illuminate\Database\QueryException;
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
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        if (! $request->filled('keyword')) {
            return response()->json(['status' => 'error', 'error' => 'Missing keyword'], 422);
        }
        $keyword = $request->input('keyword');
        if (is_numeric($keyword)) {
            $results = User::where('gtid', $keyword)->get();
        } else {
            $keyword = '%'.$request->input('keyword').'%';
            $results = User::where('uid', 'LIKE', $keyword)
                ->orWhere('first_name', 'LIKE', $keyword)
                ->orWhere('preferred_name', 'LIKE', $keyword)
                ->get();
        }

        return response()->json(['status' => 'success', 'users' => UserResource::collection($results)]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function store(Request $request)
    {
        $validations = [
            'uid' => 'required|unique:users|max:127',
            'gtid' => 'required|unique:users|max:10',
            'slack_id' => 'unique:users|max:21|nullable',
            'gt_email' => 'required|unique:users|max:255',
            'personal_email' => 'unique:users|max:255|nullable',
            'first_name' => 'required|max:127',
            'middle_name' => 'max:127',
            'last_name' => 'required|max:127',
            'preferred_name' => 'max:127',
            'phone' => 'max:15',
            'emergency_contact_name' => 'max:255',
            'emergency_contact_phone' => 'max:15',
            'join_semester' => 'max:6',
            'graduation_semester' => 'max:6',
            'shirt_size' => 'in:s,m,l,xl,xxl,xxxl|nullable',
            'polo_size' => 'in:s,m,l,xl,xxl,xxxl|nullable',
            'accept_safety_agreement' => 'date|nullable',
            'generateToken' => 'boolean',
        ];
        $this->validate($request, $validations);

        $user = new User();
        if ($request->input('generateToken')) {
            $user->api_token = bin2hex(openssl_random_pseudo_bytes(16));
            unset($validations['generateToken']);
        }
        foreach ($validations as $key => $value) {
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
        } else {
            return response()->json(['status' => 'error', 'message' => 'Unknown error.'], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        $include = $request->input('include');
        $user = User::findByIdentifier($id)->with($this->authorizeInclude(User::class, $include))->first();
        if ($user) {
            $requestingUser = $request->user();
            //Enforce users only viewing themselves (read-users-own)
            if ($requestingUser->cant('read-users') && $requestingUser->id != $user->id) {
                return response()->json(['status' => 'error',
                    'message' => 'Forbidden - You do not have permission to view this User.', ], 403);
            }

            //Show API tokens only to admins and the users themselves
            //TODO: Replace this with something better
            if ($requestingUser->id == $user->id || $requestingUser->hasRole('admin')) {
                $user = $user->makeVisible('api_token');
            }

            return response()->json(['status' => 'success', 'user' => new UserResource($user)]);
        } else {
            return response()->json(['status' => 'error', 'message' => 'User not found.'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $requestingUser = $request->user();
        $user = User::findByIdentifier($id)->first();
        if (! $user) {
            return response()->json(['status' => 'error', 'message' => 'User not found.'], 404);
        }

        //Enforce users only updating themselves (update-users-own)
        if ($requestingUser->cant('update-users') && $requestingUser->id != $user->id) {
            return response()->json(['status' => 'error',
                'message' => 'Forbidden - You do not have permission to update this User.', ], 403);
        }

        //Update only included fields
        $validatedFields = $this->validate($request, [
            'slack_id' => ['max:21', 'nullable', Rule::unique('users')->ignore($user->id)],
            'personal_email' => ['max:255', 'nullable', Rule::unique('users')->ignore($user->id)],
            'first_name'=> 'max:127',
            'last_name' => 'max:127',
            'middle_name' => 'max:127',
            'preferred_name' => 'max:127',
            'phone' => 'max:15',
            'emergency_contact_name' => 'max:255',
            'emergency_contact_phone' => 'max:15',
            'join_semester' => 'max:6',
            'graduation_semester' => 'max:6',
            'shirt_size' => 'in:s,m,l,xl,xxl,xxxl|nullable',
            'polo_size' => 'in:s,m,l,xl,xxl,xxxl|nullable',
            'accept_safety_agreement => date|nullable',
            'generateToken' => 'boolean',
            'gender' => 'string|nullable',
            'ethnicity' => 'string|nullable',
        ]);

        //Generate an API token for the user if requested *AND* the requesting user is self or admin
        //This is deliberately doing a separate update/save of the user model because `api_token` MUST
        //be prevented from mass assignment, otherwise weird things will happen when you `PUT` a User
        //while authenticating with an API token.
        if ($request->input('generateToken') &&
            ($requestingUser->hasRole('admin') || $requestingUser->id == $user->id)) {
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
        //TODO: Replace this with something better
        if ($requestingUser->id == $user->id || $requestingUser->hasRole('admin')) {
            $user = $user->makeVisible('api_token');
        }

        if ($user) {
            return response()->json(['status' => 'success', 'user' => new UserResource($user)]);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Unknown error.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::findByIdentifier($id)->first();
        $deleted = $user->delete();
        if ($deleted) {
            return response()->json(['status' => 'success', 'message' => 'User deleted.']);
        } else {
            return response()->json(['status' => 'error',
                'message' => 'User does not exist or was previously deleted.', ], 422);
        }
    }
}
