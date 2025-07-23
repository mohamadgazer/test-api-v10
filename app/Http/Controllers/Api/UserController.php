<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AdminLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group User Management
 *
 * APIs for managing users (Admin only)
 */
class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/users",
     *     summary="Get paginated list of users",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/UserPaginated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Forbidden")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $users = User::paginate(request('per_page', 10));
            $users->getCollection()->makeHidden(['password']);

            AdminLog::create([
                'admin_id' => Auth::id(),
                'action' => 'view_users_list',
                'target_model' => 'User',
            ]);

            return response()->json([
                'success' => true,
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve users',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/users/{id}",
     *     summary="Get specific user by ID",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of user to return",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Forbidden")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            $user->makeHidden(['password']);

            AdminLog::create([
                'admin_id' => Auth::id(),
                'action' => 'view_user',
                'target_model' => 'User',
                'target_id' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/users",
     *     summary="Create a new user",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","role"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="phone", type="string", example="+1234567890", nullable=true),
     *             @OA\Property(property="address", type="string", example="123 Main St", nullable=true),
     *             @OA\Property(property="is_admin", type="boolean", example=true),
     *             @OA\Property(property="role", type="string", enum={"viewer","manager","super_admin","customer"}, example="manager")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/User"),
     *             @OA\Property(property="message", type="string", example="User created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Forbidden")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:255',
                'is_admin' => 'sometimes|boolean',
                'role' => 'required|string|in:viewer,manager,super_admin,customer',
            ]);

            $validated['password'] = bcrypt($validated['password']);
            
            // Set is_admin based on role
            if (!isset($validated['is_admin'])) {
                $validated['is_admin'] = $validated['role'] !== 'customer';
            }

            $user = User::create($validated);

            AdminLog::create([
                'admin_id' => Auth::id(),
                'action' => 'create_user',
                'target_model' => 'User',
                'target_id' => $user->id,
                'data' => $user->only(['name', 'email', 'phone', 'address', 'is_admin', 'role']),
            ]);

            return response()->json([
                'success' => true,
                'data' => $user->makeHidden(['password']),
                'message' => 'User created successfully'
            ], Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Put(
     *     path="/users/{id}",
     *     summary="Update an existing user",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of user to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe Updated", nullable=true),
     *             @OA\Property(property="email", type="string", format="email", example="john.updated@example.com", nullable=true),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword123", nullable=true),
     *             @OA\Property(property="phone", type="string", example="+9876543210", nullable=true),
     *             @OA\Property(property="address", type="string", example="456 Updated St", nullable=true),
     *             @OA\Property(property="is_admin", type="boolean", example=false, nullable=true),
     *             @OA\Property(property="role", type="string", enum={"viewer","manager","super_admin","customer"}, example="manager", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/User"),
     *             @OA\Property(property="message", type="string", example="User updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Forbidden")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            $currentUser = Auth::user();

            // Super admin protection
            if ($user->role === 'super_admin' && $currentUser->id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot modify another super admin.'
                ], Response::HTTP_FORBIDDEN);
            }

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,'.$id,
                'password' => 'sometimes|string|min:8',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:255',
                'is_admin' => 'sometimes|boolean',
                'role' => 'sometimes|string|in:viewer,manager,super_admin,customer',
            ]);

            if (isset($validated['password'])) {
                $validated['password'] = bcrypt($validated['password']);
            }

            // Role-based logic
            if (isset($validated['role'])) {
                if ($validated['role'] === 'customer') {
                    $validated['is_admin'] = false;
                } elseif (!isset($validated['is_admin'])) {
                    $validated['is_admin'] = true;
                }
            }

            $user->update($validated);

            AdminLog::create([
                'admin_id' => $currentUser->id,
                'action' => 'update_user',
                'target_model' => 'User',
                'target_id' => $user->id,
                'data' => $validated,
            ]);

            return response()->json([
                'success' => true,
                'data' => $user->makeHidden(['password']),
                'message' => 'User updated successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Delete(
     *     path="/users/{id}",
     *     summary="Delete a user",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of user to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Forbidden")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            $currentUser = Auth::user();

            // Prevent deleting yourself
            if ($currentUser->id === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account'
                ], Response::HTTP_FORBIDDEN);
            }

            // Super admin protection
            if ($user->role === 'super_admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete a super admin'
                ], Response::HTTP_FORBIDDEN);
            }

            $user->delete();

            AdminLog::create([
                'admin_id' => $currentUser->id,
                'action' => 'delete_user',
                'target_model' => 'User',
                'target_id' => $id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/admins",
     *     summary="Get paginated list of admins",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/UserPaginated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Forbidden")
     *         )
     *     )
     * )
     */
    public function admins(): JsonResponse
    {
        try {
            $admins = User::where('is_admin', true)->paginate(request('per_page', 10));
            $admins->getCollection()->makeHidden(['password']);

            AdminLog::create([
                'admin_id' => Auth::id(),
                'action' => 'view_admins_list',
                'target_model' => 'User',
            ]);

            return response()->json([
                'success' => true,
                'data' => $admins
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve admins',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Put(
     *     path="/me",
     *     summary="Update authenticated user's profile",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Name", nullable=true),
     *             @OA\Property(property="email", type="string", format="email", example="updated@example.com", nullable=true),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword123", nullable=true),
     *             @OA\Property(property="phone", type="string", example="+9876543210", nullable=true),
     *             @OA\Property(property="address", type="string", example="456 Updated St", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/User"),
     *             @OA\Property(property="message", type="string", example="Profile updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function updateSelf(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,'.$user->id,
                'password' => 'sometimes|string|min:8',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:255',
            ]);

            if (isset($validated['password'])) {
                $validated['password'] = bcrypt($validated['password']);
            }

            $user->update($validated);

            AdminLog::create([
                'admin_id' => $user->id,
                'action' => 'update_self',
                'target_model' => 'User',
                'target_id' => $user->id,
                'data' => $validated,
            ]);

            return response()->json([
                'success' => true,
                'data' => $user->makeHidden(['password']),
                'message' => 'Profile updated successfully'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}