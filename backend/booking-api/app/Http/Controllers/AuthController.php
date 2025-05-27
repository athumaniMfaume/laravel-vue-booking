<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
public function register(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name'     => 'required|string|max:255',
        'email'    => 'required|string|email|unique:users,email',
        'password' => 'required|string',
        'role'     => 'required|string|in:user,business,admin',  // ← validate that role
    ]);

if ($validator->fails()) {
    return response()->json([
        'message' => 'Validation failed',
        'errors' => $validator->errors()
    ], 422);

}

    $user = User::create([
        'name'     => $request->name,
        'email'    => $request->email,
        'password' => Hash::make($request->password),
        'role'     => $request->role,                        // ← assign the role
    ]);

    return response()->json([
        'message' => 'User successfully registered!',
        'user'    => $user
    ], 201);
}


public function login(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email'    => 'required|email',
        'password' => 'required|string',
    ]);

if ($validator->fails()) {
    return response()->json([
        'message' => 'Validation failed',
        'errors' => $validator->errors()
    ], 422);
}

    if (! Auth::attempt($validator->validated())) {
        return response()->json(['error' => 'Invalid credentials!'], 401);
    }

    $user  = Auth::user();
    $token = $user->createToken('auth-token')->plainTextToken;

    // Determine where to send them
    $redirectUrl = match ($user->role) {
        'admin'    => '/admin/dashboard',
        'business' => '/business/dashboard',
        'user'     => '/user/dashboard',
        default    => '/',
    };

    return response()->json([
        'message'     => 'User successfully logged in',
        'access_token'=> $token,
        'token_type'  => 'Bearer',
        'user'        => $user,
        'redirect_to' => $redirectUrl,
    ], 200);
}


        public function logout(Request $request)
    {
        // Revoke the current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'User successfully logged out',
        ]);
    }
}
