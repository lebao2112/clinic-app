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

        // Attempt to authenticate the user
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => Message::INVALID_CREDENTIALS], 401);
        }

        // Retrieve the authenticated user
        $user = User::where('email', $request->email)->firstOrFail();
        
        // Generate a new API token
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