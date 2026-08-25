<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Constants\Message; 
use App\Http\Resources\UserResource; 

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return response()->json(['message' => Message::INVALID_CREDENTIALS], 401);
        }
        if (!$user->is_active) {
            return response()->json([
                'message' => Message::ACCOUNT_LOCKED
            ], 403);
        }
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return the token response
        return response()->json([
            'message' => Message::LOGIN_SUCCESS, 
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('role.permissions');
        return new UserResource($user);
    }
    
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json(['message' => Message::LOGOUT_SUCCESS]);
    }
}