<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class RoleController implements HasMiddleware
{
    #[\Override]
    public static function middleware(): array
    {
        return [
            'role:admin',
        ];
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
            $role->givePermissionTo($request->input('permissions'));
        }

        $dbRole = Role::where('id', $role->id)->with('permissions')->first();

        return response()->json(['status' => 'success', 'role' => $dbRole]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $name): JsonResponse
    {
        $role = Role::findByName($name)->with('permissions')->first();

        return response()->json(['status' => 'success', 'role' => $role]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $name): JsonResponse
    {
        $role = Role::findByName($name);

        $request->validate([
            'name' => Rule::unique('roles')->ignore($role->id),
        ]);

        if ($request->filled('name')) {
            $role->name = $request->input('name');
            $role->save();
        }

        if ($request->filled('permissions')) {
            $role->syncPermissions($request->input('permissions'));
        }

        $dbRole = Role::where('id', $role->id)->with('permissions')->first();

        return response()->json(['status' => 'success', 'role' => $dbRole]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $name): JsonResponse
    {
        $role = Role::findByName($name);

        $role->delete();

        return response()->json(['status' => 'success', 'message' => 'Role deleted.'], 200);
    }

    /**
     * Assigns roles to users.
     */
    public function assign(string $name, Request $request): JsonResponse
    {
        $role = Role::findByName($name);

        if (! $request->filled('users')) {
            return response()->json(['status' => 'error',
                'message' => 'You must specify users to assign to a role.',
            ], 422);
        }

        foreach ($request->input('users') as $user) {
            $dbUser = User::findByIdentifier($user)->first();
            if ($dbUser === null) {
                return response()->json(['status' => 'error', 'message' => 'User '.$user.' not found.'], 422);
            }

            $dbUser->assignRole($role);
        }

        return response()->json(['status' => 'success']);
    }
}
