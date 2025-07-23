<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;

/**
 * Class RegisterController
 *
 * Handles the registration of new users.
 *
 * @package App\Http\Controllers\Auth
 */
class RegisterController extends Controller
{
    /**
     * Register a new user.
     *
     * @param  RegisterRequest  $request  The validated registration request.
     * @return JsonResponse  A JSON response containing the new user data.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        // Validate the incoming request data
        $validated = $request->validated();

        // Create the new user using hashed password
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone'    => $validated['phone'] ?? null,
            'address'  => $validated['address'] ?? null,
        ]);

        // Return JSON response with created user
        return response()->json([
            'message' => 'User registered successfully',
            'user'    => $user,
        ], 201);
    }
}
