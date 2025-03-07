<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Http\Requests\StorePermissionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller implements HasMiddleware
{
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
        $permissions = Permission::all();

        return response()->json(['status' => 'success', 'permissions' => $permissions]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePermissionRequest $request): JsonResponse
    {
        $name = $request->input('name');
        $permission = new Permission();
        $permission->name = $name;
        $permission->save();

        $roles = $request->input('roles');
        if (is_array($roles)) {
            foreach ($roles as $role) {
                $dbRole = Role::findByName($role);
                $dbRole->givePermissionTo($permission->name);
            }
        }

        $dbPermission = Permission::find($permission->id);

        return response()->json(['status' => 'success', 'permission' => $dbPermission]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $name): JsonResponse
    {
        $permission = Permission::findByName($name)->with('roles')->first();

        return response()->json(['status' => 'success', 'permission' => $permission]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $name): JsonResponse
    {
        $permission = Permission::findByName($name);
        $permission->name = $request->input('name');
        $permission->save();

        $dbPermission = Permission::find($permission->id);

        return response()->json(['status' => 'success', 'permission' => $dbPermission]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $name): JsonResponse
    {
        $permission = Permission::findByName($name);
        $permission->delete();

        return response()->json(['status' => 'success', 'message' => 'Permission deleted.'], 200);
    }
}
