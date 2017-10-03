<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $roles = Role::all();
        return response()->json(['status' => 'success', 'roles' => $roles]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name'=>'required|unique:roles',
        ]);
        
        $role = new Role();
        $role->name = $request->input('name');
        $role->save();
        $dbRole = Role::find($role->id);
        
        if ($request->has('permissions')) {
            foreach ($request->input('permissions') as $permission) {
                $dbPerm = Permission::find($permission);
                if ($dbPerm) {
                    $dbRole->givePermissionTo($dbPerm);
                } else {
                    return response()->json(['status' => 'error', 'message' => 'Permission not found.'], 422);
                }
            }
        }
        
        return response()->json(['status' => 'success', 'role' => $dbRole]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $role = Role::find($id);
        if (!$id) {
            return response()->json(['status' => 'error', 'message' => 'Role not found.'], 404);
        }
        return response()->json(['status' => 'success', 'role' => $role]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $role = Role::find($id);
        if (!$role) {
            return response()->json(['status' => 'error', 'message' => 'Role not found.'], 404);
        }
        
        $this->validate($request, [
            'name' => Rule::unique('roles')->ignore($id),
        ]);
        
        if ($request->has('name')) {
            $role->name = $request->input('name');
            $role->save();
        }
        
        if ($request->has('permissions')) {
            $role->syncPermissions($request->input('permissions'));
        }
        
        return response()->json(['status' => 'success', 'role' => $role]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $role = Role::find($id);
        if (!$role) {
            return response()->json(['status' => 'error', 'Role not found.'], 404);
        }
        
        $role->delete();
        return response()->json(['status' => 'success', 'message' => 'Role deleted.'], 200);
    }
}
