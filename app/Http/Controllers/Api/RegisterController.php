<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="User registration and authentication endpoints"
 * )
 */
class RegisterController extends Controller
{
    /**
     * Register a new user
     *
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", maxLength=255, example="user@example.com"),
     *             @OA\Property(property="password", type="string", minLength=8, example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123"),
     *             @OA\Property(property="phone", type="string", nullable=true, maxLength=20, example="+1234567890"),
     *             @OA\Property(property="address", type="string", nullable=true, example="123 Main St")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User registered successfully"),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *                 @OA\Property(property="phone", type="string", nullable=true, example="+1234567890"),
     *                 @OA\Property(property="address", type="string", nullable=true, example="123 Main St"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={
     *                     "name": {"The name field is required."},
     *                     "email": {"The email must be a valid email address.", "The email has already been taken."},
     *                     "password": {"The password must be at least 8 characters."}
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     *
     * @param RegisterRequest $request The validated registration request
     * @return JsonResponse A JSON response containing the new user data
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
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
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal server error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *     @OA\Property(property="phone", type="string", nullable=true, example="+1234567890"),
 *     @OA\Property(property="address", type="string", nullable=true, example="123 Main St"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */