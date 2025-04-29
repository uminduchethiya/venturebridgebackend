<?php

namespace App\Http\Controllers;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
class AuthController extends Controller
{



    public function registrationindex(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Register page loaded successfully.'
            ], 200);
        }
        return response()->view('auth.register', [], 200);
    }



    public function register(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|unique:users',
                'password' => 'required|confirmed|min:6',
                'company_name' => 'required|string|max:255',
                'type' => 'required|in:startup,investor',
            ]);

            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'company_name' => $request->company_name,
                'type' => $request->type,
            ]);

            Auth::login($user);

            return response()->json([
                'message' => 'User registered successfully.',
                'user' => $user,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }
    }


    public function login(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check credentials
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        // Login user
        Auth::login($user);

        // Create Sanctum token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }
    public function loginindex()
    {
        return response()->json([
            'message' => 'Registration page loaded successfully.',
        ], 200);

        //return response()->view('auth.login', [], 200);
    }

    public function forgotPasswordIndex(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Forgot password page loaded successfully.'
            ], 200);
        }

        return response()->view('forgot-password', [], 200);
    }

    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $status = Password::sendResetLink($request->only('email'));

            return $status === Password::RESET_LINK_SENT
                ? response()->json(['status' => true, 'message' => 'Reset link sent successfully.'])
                : response()->json(['status' => false, 'message' => 'Failed to send reset link.'], 500);
        } catch (\Exception $e) {
            // Log error to Laravel log file
            Log::error('Reset Link Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Exception occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }

}
}