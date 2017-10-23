<?php

namespace App\Http\Controllers;

use App\Transformers\PermissionTransformer;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Traits\FractalResponse;

class PermissionController extends Controller
{
    use FractalResponse;
    
    public function __construct()
    {
        $this->middleware('role:admin');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $permissions = Permission::all();
        $fr = $this->fractalResponse($permissions, new PermissionTransformer(), $request->input('include'));
        return response()->json(['status' => 'success', 'permissions' => $fr]);
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
            'name'=>'required|unique:permissions',
        ]);

        $name = $request->input('name');
        $permission = new Permission();
        $permission->name = $name;
        $permission->save();

        $roles = $request->input('roles');
        if (!empty($roles)) {
            foreach ($roles as $role) {
                try {
                    $dbRole = Role::findByName($role);
                } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
                    return response()->json(['status' => 'error', 'message' => "Role '$role' not found."], 404);
                } catch (\Exception $e) {
                    return response()->json(['status' => 'error', 'message' => 'An internal error occurred.'], 500);
                }
                $dbRole->givePermissionTo($permission->name);
            }
        }
        
        $dbPermission = Permission::find($permission->id);
        $fr = $this->fractalResponse($dbPermission, new PermissionTransformer(), $request->input('include'));
        return response()->json(['status' => 'success', 'permission' => $fr]);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $name
     * @return \Illuminate\Http\Response
     */
    public function show($name, Request $request)
    {
        try {
            $permission = Permission::findByName($name)->with('roles')->first();
        } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
            return response()->json(['status' => 'error',
                'message' => "Permission '$name' not found."], 404);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'An internal error occurred.'], 500);
        }

        $fr = $this->fractalResponse($permission, new PermissionTransformer(), $request->input('include'));
        return response()->json(['status' => 'success', 'permission' => $fr]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $name
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $name)
    {
        try {
            $permission = Permission::findByName($name);
            $permission->name = $request->input('name');
            $permission->save();
        } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
            return response()->json(['status' => 'error',
                'message' => "Permission '$name' not found."], 404);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'An internal error occurred.'], 500);
        }
        
        $dbPermission = Permission::find($permission->id);
        $fr = $this->fractalResponse($dbPermission, new PermissionTransformer(), $request->input('include'));
        return response()->json(['status' => 'success', 'permission' => $fr]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $name
     * @return \Illuminate\Http\Response
     */
    public function destroy($name)
    {
        try {
            $permission = Permission::findByName($name);
            $permission->delete();
        } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
            return response()->json(['status' => 'error',
                'message' => "Permission '$name' not found."], 404);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'An internal error occurred.'], 500);
        }
        
        return response()->json(['status' => 'success', 'message' => 'Permission deleted.'], 200);
    }
}
