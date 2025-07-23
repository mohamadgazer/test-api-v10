<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * @group Authentication
 *
 * APIs for user authentication (register, login, logout)
 */
class AuthController extends Controller
{
    /**
     * Register a new user
     *
     * @bodyParam name string required User name. Example: John Doe
     * @bodyParam email string required Valid email. Example: john@example.com
     * @bodyParam password string required Password (min 6 characters). Example: secret123
     *
     * @response 201 {
     *   "user": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "created_at": "2025-07-15T23:59:00.000000Z",
     *     "updated_at": "2025-07-15T23:59:00.000000Z"
     *   },
     *   "token": "generated_token_here"
     * }
     *
     * @response 422 {
     *   "errors": {
     *     "email": ["The email has already been taken."]
     *   }
     * }
     */
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|unique:users,email',
                'password' => 'required|string|min:6'
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Unexpected error occurred during registration.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login a user and get token
     *
     * @bodyParam email string required Email address. Example: john@example.com
     * @bodyParam password string required User password. Example: secret123
     *
     * @response 200 {
     *   "access_token": "token_here",
     *   "token_type": "Bearer",
     *   "user": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "is_admin": false,
     *     "role": "user",
     *     "phone": null,
     *     "address": null,
     *     "created_at": "...",
     *     "updated_at": "..."
     *   }
     * }
     *
     * @response 401 {
     *   "message": "Invalid credentials"
     * }
     *
     * @response 422 {
     *   "errors": {
     *     "email": ["The email field is required."],
     *     "password": ["The password field is required."]
     *   }
     * }
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_admin' => $user->is_admin,
                    'role' => $user->role,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Unexpected error occurred during login.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Logout the authenticated user
     *
     * @authenticated
     *
     * @response 200 {
     *   "message": "Logged out"
     * }
     *
     * @response 500 {
     *   "message": "Unexpected error occurred during logout.",
     *   "error": "Exception message"
     * }
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json(['message' => 'Logged out']);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Unexpected error occurred during logout.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
