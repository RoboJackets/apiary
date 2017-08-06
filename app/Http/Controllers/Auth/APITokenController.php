<?php

namespace App\Http\Controllers\Auth;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTFactory;

class APITokenController extends Controller
{
    public function getToken(Request $request)
    {
        try {
            $customClaims = ['iat' => time(), 'exp' => time() + 60, 'nbf' => time() - 60, 'sub' => cas()->user()];
            $payload = JWTFactory::make($customClaims);
            $token = JWTAuth::encode($payload)->get();
            return response()->json(compact('token'));
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        return response()->json(['error' => 'could_not_create_token'], 500);
    }
}
