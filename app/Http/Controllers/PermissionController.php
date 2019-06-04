<?php declare(strict_types = 1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $permissions = Permission::all();

        return response()->json(['status' => 'success', 'permissions' => $permissions]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $this->validate($request, [
            'name' => 'required|unique:permissions',
        ]);

        $name = $request->input('name');
        $permission = new Permission();
        $permission->name = $name;
        $permission->save();

        $roles = $request->input('roles');
        if (is_array($roles)) {
            foreach ($roles as $role) {
                try {
                    $dbRole = Role::findByName($role);
                } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
                    Bugsnag::notifyException($e);

                    return response()->json(['status' => 'error', 'message' => 'Role ' . $role . ' not found.'], 404);
                } catch (\Throwable $e) {
                    Bugsnag::notifyException($e);

                    return response()->json(['status' => 'error', 'message' => 'An internal error occurred.'], 500);
                }
                $dbRole->givePermissionTo($permission->name);
            }
        }

        $dbPermission = Permission::find($permission->id);

        return response()->json(['status' => 'success', 'permission' => $dbPermission]);
    }

    /**
     * Display the specified resource.
     *
     * @param string  $name
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $name): JsonResponse
    {
        try {
            $permission = Permission::findByName($name)->with('roles')->first();
        } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
            Bugsnag::notifyException($e);

            return response()->json(['status' => 'error',
                'message' => 'Permission ' . $name . ' not found.',
            ], 404);
        } catch (\Throwable $e) {
            Bugsnag::notifyException($e);

            return response()->json(['status' => 'error', 'message' => 'An internal error occurred.'], 500);
        }

        return response()->json(['status' => 'success', 'permission' => $permission]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request  $request
     * @param string  $name
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $name): JsonResponse
    {
        try {
            $permission = Permission::findByName($name);
            $permission->name = $request->input('name');
            $permission->save();
        } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
            Bugsnag::notifyException($e);

            return response()->json(['status' => 'error',
                'message' => 'Permission ' . $name . ' not found.',
            ], 404);
        } catch (\Throwable $e) {
            Bugsnag::notifyException($e);

            return response()->json(['status' => 'error', 'message' => 'An internal error occurred.'], 500);
        }

        $dbPermission = Permission::find($permission->id);

        return response()->json(['status' => 'success', 'permission' => $dbPermission]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int  $name
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $name): JsonResponse
    {
        try {
            $permission = Permission::findByName($name);
            $permission->delete();
        } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
            Bugsnag::notifyException($e);

            return response()->json(['status' => 'error',
                'message' => 'Permission ' . $name . ' not found.',
            ], 404);
        } catch (\Throwable $e) {
            Bugsnag::notifyException($e);

            return response()->json(['status' => 'error', 'message' => 'An internal error occurred.'], 500);
        }

        return response()->json(['status' => 'success', 'message' => 'Permission deleted.'], 200);
    }
}
