<?php

namespace app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Constants\Message; 

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
        // Return the currently authenticated user
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json(['message' => Message::LOGOUT_SUCCESS]);
    }
}