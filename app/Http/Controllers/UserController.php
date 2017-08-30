<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     *
     * @api {get} /users/ List all users
     * @apiGroup Users
     */
    public function index()
    {
        $users = User::all();
        return response()->json(['status' => 'success', 'users' => $users]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'uid' => 'required|unique:users|max:127',
            'gtid' => 'required|unique:users|max:10',
            'slack_id' => 'unique:users|max:21',
            'gt_email' => 'required|unique:users|max:255',
            'personal_email' => 'unique:users|max:255',
            'first_name' => 'required|max:127',
            'middle_name' => 'max:127',
            'last_name' => 'required|max:127',
            'preferred_name' => 'max:127',
            'phone' => 'max:15',
            'emergency_contact_name' => 'max:255',
            'emergency_contact_phone' => 'max:15',
            'join_semester' => 'max:6',
            'graduation_semester' => 'max:6',
            'shirt_size' => 'in:s,m,l,xl,xxl,xxxl',
            'polo_size' => 'in:s,m,l,xl,xxl,xxxl',
        ]);

        try {
            $user = User::create($request->all());
        } catch (QueryException $e) {
            $errorMessage = $e->errorInfo[2];
            return response()->json(['status' => 'error', 'message' => $errorMessage], 500);
        }

        if (is_numeric($user->id)) {
            $dbUser = User::findOrFail($user->id);
            return response()->json(['status' => 'success', 'user' => $dbUser], 201);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Unknown error.'], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        $user = $this->getUserByIdentifier($id);
        if ($user) {
            return response()->json(['status' => 'success', 'user' => $user]);
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
    public function update(Request $request, $id)
    {
        $user = $this->getUserByIdentifier($id);
        //Update only included fields
        $this->validate($request, [
            'uid' => ['max:127', Rule::unique('users')->ignore($user->id)],
            'gtid' => ['max:10', Rule::unique('users')->ignore($user->id)],
            'slack_id' => ['max:21', Rule::unique('users')->ignore($user->id)],
            'gt_email' => ['max:255', Rule::unique('users')->ignore($user->id)],
            'personal_email' => ['max:255', Rule::unique('users')->ignore($user->id)],
            'first_name' => 'max:127',
            'middle_name' => 'max:127',
            'last_name' => 'max:127',
            'preferred_name' => 'max:127',
            'phone' => 'max:15',
            'emergency_contact_name' => 'max:255',
            'emergency_contact_phone' => 'max:15',
            'join_semester' => 'max:6',
            'graduation_semester' => 'max:6',
            'shirt_size' => 'in:s,m,l,xl,xxl,xxxl',
            'polo_size' => 'in:s,m,l,xl,xxl,xxxl',
        ]);

        $user->update($request->all());

        $user = User::find($user->id);
        if ($user) {
            return response()->json(['status' => 'success', 'user' => $user]);
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
        $user = $this->getUserByIdentifier($id);
        $deleted = $user->delete();
        if ($deleted) {
            return response()->json(['status' => 'success', 'message' => 'User deleted.']);
        } else {
            return response()->json(['status' => 'error',
                'message' => 'User does not exist or was previously deleted.'], 422);
        }
    }

    /**
     * Retrieves a user model based upon a given identifier
     *
     * @param $id string Identifier for user (DB ID, GTID, UID)
     * @return mixed
     */
    public static function getUserByIdentifier($id)
    {
        if (is_numeric($id) && strlen($id) == 9 && $id[0] == 9) {
            return User::where('gtid', $id)->first();
        } elseif (is_numeric($id)) {
            return User::find($id);
        } elseif (!is_numeric($id)) {
            return User::where('uid', $id)->first();
        } else {
            return null;
        }
    }
}
