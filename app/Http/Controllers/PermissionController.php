<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
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
        $permissions = Permission::all();
        return response()->json(['status' => 'success', 'permissions' => $permissions]);
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
            'name'=>'required|max:40',
        ]);

        $name = $request->input('name');
        $permission = new Permission();
        $permission->name = $name;
        $permission->save();

        $roles = $request->input('roles');
        if (!empty($roles)) {
            foreach ($roles as $role) {
                $dbRole = Role::where('id', '=', $role)->first();
                if ($dbRole) {
                    $dbPerm = Permission::find($permission->id);
                    $dbRole->givePermissionTo($dbPerm);
                } else {
                    return response()->json(['status' => 'error', 'message' => "Role $role not found."], 422);
                }
            }
        }
        
        $dbPermission = Permission::find($permission->id);
        return response()->json(['status' => 'success', 'permission' => $dbPermission]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $permission = Permission::where('id', $id)->with('roles')->first();
        if (!$permission) {
            return response()->json(['status' => 'error', 'message' => 'Permission not found.'], 404);
        }
        return response()->json(['status' => 'success', 'permission' => $permission]);
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
        $permission = Permission::find($id);
        if (!$permission) {
            return response()->json(['status' => 'error', 'message' => 'Permission not found.'], 404);
        }
        $permission->name = $request->input('name');
        $permission->save();
        
        $dbPermission = Permission::find($id);
        return response()->json(['status' => 'success', 'permission' => $dbPermission]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $permission = Permission::find($id);
        if (!$permission) {
            return response()->json(['status' => 'error', 'message' => 'Permission not found.'], 404);
        }
        
        $permission->delete();
        return response()->json(['status' => 'success', 'message' => 'Permission deleted.'], 200);
    }
}
