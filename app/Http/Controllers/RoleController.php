<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Models\User;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $roles = Role::all();

        return response()->json(['status' => 'success', 'roles' => $roles]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = new Role();
        $role->name = $request->input('name');
        $role->save();

        if ($request->filled('permissions')) {
            try {
                $role->givePermissionTo($request->input('permissions'));
            } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
                Bugsnag::notifyException($e);

                return response()->json(['status' => 'error',
                    'message' => $e->getMessage(),
                ], 422);
            } catch (\Throwable $e) {
                Bugsnag::notifyException($e);

                return response()->json(['status' => 'error', 'message' => 'An internal error occurred.'], 500);
            }
        }

        $dbRole = Role::where('id', $role->id)->with('permissions')->first();

        return response()->json(['status' => 'success', 'role' => $dbRole]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $name): JsonResponse
    {
        try {
            // @phan-suppress-next-line PhanPossiblyUndeclaredMethod
            $role = Role::findByName($name)->with('permissions')->first();
        } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
            Bugsnag::notifyException($e);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 404);
        } catch (\Throwable $e) {
            Bugsnag::notifyException($e);

            return response()->json(['status' => 'error', 'message' => 'An internal error occurred.'], 500);
        }

        return response()->json(['status' => 'success', 'role' => $role]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $name): JsonResponse
    {
        try {
            $role = Role::findByName($name);
        } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
            Bugsnag::notifyException($e);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 404);
        } catch (\Throwable $e) {
            Bugsnag::notifyException($e);

            return response()->json(['status' => 'error', 'message' => 'An internal error occurred.'], 500);
        }

        $this->validate($request, [
            'name' => Rule::unique('roles')->ignore($role->id),
        ]);

        if ($request->filled('name')) {
            $role->name = $request->input('name');
            // @phan-suppress-next-line PhanPossiblyUndeclaredMethod
            $role->save();
        }

        if ($request->filled('permissions')) {
            try {
                // @phan-suppress-next-line PhanPossiblyUndeclaredMethod
                $role->syncPermissions($request->input('permissions'));
            } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
                Bugsnag::notifyException($e);

                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
            } catch (\Throwable $e) {
                Bugsnag::notifyException($e);

                return response()->json(['status' => 'error', 'message' => 'An internal error occurred.'], 500);
            }
        }

        $dbRole = Role::where('id', $role->id)->with('permissions')->first();

        return response()->json(['status' => 'success', 'role' => $dbRole]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $name): JsonResponse
    {
        try {
            $role = Role::findByName($name);
        } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
            Bugsnag::notifyException($e);

            return response()->json(['status' => 'error', 'message' => 'Role not found.'], 404);
        } catch (\Throwable $e) {
            Bugsnag::notifyException($e);

            return response()->json(['status' => 'error', 'message' => 'An internal error occurred.'], 500);
        }

        // @phan-suppress-next-line PhanPossiblyUndeclaredMethod
        $role->delete();

        return response()->json(['status' => 'success', 'message' => 'Role deleted.'], 200);
    }

    /**
     * Assigns roles to users.
     */
    public function assign(string $name, Request $request): JsonResponse
    {
        try {
            $role = Role::findByName($name);
        } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
            Bugsnag::notifyException($e);

            return response()->json(['status' => 'error', 'message' => 'Role not found.'], 404);
        } catch (\Throwable $e) {
            Bugsnag::notifyException($e);

            return response()->json(['status' => 'error', 'message' => 'An internal error occurred.'], 500);
        }

        if (! $request->filled('users')) {
            return response()->json(['status' => 'error',
                'message' => 'You must specify users to assign to a role.',
            ], 422);
        }

        foreach ($request->input('users') as $user) {
            $dbUser = User::findByIdentifier($user)->first();
            if (null === $dbUser) {
                return response()->json(['status' => 'error', 'message' => 'User '.$user.' not found.'], 422);
            }

            $dbUser->assignRole($role);
        }

        return response()->json(['status' => 'success']);
    }
}
