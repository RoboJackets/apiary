<?php

namespace App\Http\Controllers;

use App\Transformers\RoleTransformer;
use App\User;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Traits\FractalResponse;

class RoleController extends Controller
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
        $roles = Role::all();
        $fr = $this->fractalResponse($roles, new RoleTransformer(), $request->input('include'));
        return response()->json(['status' => 'success', 'roles' => $fr]);
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
        
        if ($request->has('permissions')) {
            try {
                $role->givePermissionTo($request->input('permissions'));
            } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
                return response()->json(['status' => 'error',
                    'message' => $e->getMessage()], 422);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'An internal error occurred.'], 500);
            }
        }
        
        $dbRole = Role::where('id', $role->id)->with('permissions')->first();
        $fr = $this->fractalResponse($dbRole, new RoleTransformer(), $request->input('include'));
        return response()->json(['status' => 'success', 'role' => $fr]);
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
            $role = Role::findByName($name)->with('permissions')->first();
        } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'An internal error occurred.'], 500);
        }

        $fr = $this->fractalResponse($role, new RoleTransformer(), $request->input('include'));
        return response()->json(['status' => 'success', 'role' => $fr]);
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
            $role = Role::findByName($name);
        } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'An internal error occurred.'], 500);
        }
        
        $this->validate($request, [
            'name' => Rule::unique('roles')->ignore($role->id),
        ]);
        
        if ($request->has('name')) {
            $role->name = $request->input('name');
            $role->save();
        }
        
        if ($request->has('permissions')) {
            try {
                $role->syncPermissions($request->input('permissions'));
            } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'An internal error occurred.'], 500);
            }
        }
        
        $dbRole = Role::where('id', $role->id)->with('permissions')->first();
        $fr = $this->fractalResponse($dbRole, new RoleTransformer(), $request->input('include'));
        return response()->json(['status' => 'success', 'role' => $fr]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $name
     * @return \Illuminate\Http\Response
     */
    public function destroy($name)
    {
        try {
            $role = Role::findByName($name);
        } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
            return response()->json(['status' => 'error', 'message' => 'Role not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'An internal error occurred.'], 500);
        }
        
        $role->delete();
        return response()->json(['status' => 'success', 'message' => 'Role deleted.'], 200);
    }

    /**
     * Assigns roles to users
     *
     * @param string $name
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function assign($name, Request $request)
    {
        try {
            $role = Role::findByName($name);
        } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
            return response()->json(['status' => 'error', 'message' => 'Role not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'An internal error occurred.'], 500);
        }
        
        if (!$request->has('users')) {
            return response()->json(['status' => 'error',
                'message' => 'You must specify users to assign to a role.'], 422);
        }
        
        foreach ($request->input('users') as $user) {
            $dbUser = User::findByIdentifier($user)->first();
            if ($dbUser) {
                $dbUser->assignRole($role);
            } else {
                return response()->json(['status' => 'error', 'message' => "User '$user' not found."], 422);
            }
        }
        
        return response()->json(['status' => 'success']);
    }
}
