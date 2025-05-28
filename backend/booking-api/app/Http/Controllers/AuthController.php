<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;



class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|unique:users,email',
            'password' => 'required|string',
            'role'     => 'required|string|in:user,business,admin',
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
            'role'     => $request->role,
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
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'User successfully logged out',
        ]);
    }


    // Forgot password: send reset link
public function forgotPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
    ]);

    // Attempt to get the user
    $user = \App\Models\User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['message' => 'User not found.'], 404);
    }

    // Create token
    $token = Password::createToken($user);

    // Generate frontend URL
    $frontendUrl = 'http://localhost:8080/reset-password?token=' . $token . '&email=' . urlencode($user->email);

    // Send email directly without Mailable
    try {
        Mail::raw("Click here to reset your password: $frontendUrl", function ($message) use ($user) {
            $message->to($user->email);
            $message->subject('Reset Your Password');
        });

        return response()->json(['message' => 'Reset link sent to your email.']);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Failed to send email.', 'error' => $e->getMessage()], 500);
    }
}


    // Reset password
public function resetPassword(Request $request)
{
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|confirmed',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->forceFill([
                'password' => bcrypt($password),
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        }
    );

    return $status === Password::PASSWORD_RESET
        ? response()->json(['message' => 'Password reset successfully.'])
        : response()->json(['message' => __($status)], 500);
}
}
