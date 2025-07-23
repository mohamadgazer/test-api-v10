<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AdminLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Display a paginated list of users.
     */
    public function index(): JsonResponse
    {
        $users = User::paginate(request('per_page', 10));
        $users->getCollection()->makeHidden(['password']);

        AdminLog::create([
            'admin_id' => auth()->id(),
            'action' => 'view_users_list',
            'target_model' => 'User',
        ]);

        return response()->json($users);
    }

    /**
     * Display a specific user.
     */
    public function show($id): JsonResponse
    {
        $user = User::findOrFail($id);

        AdminLog::create([
            'admin_id' => auth()->id(),
            'action' => 'view_user',
            'target_model' => 'User',
            'target_id' => $user->id,
        ]);

        return response()->json($user->makeHidden(['password']));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'is_admin' => 'boolean',
            'role' => 'required|string|in:viewer,manager,super_admin',
        ]);

        $validated['password'] = bcrypt($validated['password']);

        $user = User::create($validated);

        AdminLog::create([
            'admin_id' => auth()->id(),
            'action' => 'create_user',
            'target_model' => 'User',
            'target_id' => $user->id,
            'data' => $user->only(['name', 'email', 'phone', 'address', 'is_admin', 'role']),
        ]);

        return response()->json($user->makeHidden(['password']), 201);
    }

    /**
     * Update a specific user.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:6',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'is_admin' => 'boolean',
            'role' => 'sometimes|string|in:viewer,manager,super_admin,customer',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        // Super admin protection
        if ($user->role === 'super_admin' && auth()->id() !== $user->id) {
            return response()->json(['message' => 'You cannot modify another super admin.'], 403);
        }

        // Role-based logic
        if (isset($validated['role']) && $validated['role'] === 'customer') {
            $validated['is_admin'] = false;
        } elseif (!isset($validated['is_admin'])) {
            $validated['is_admin'] = true;
        }

        $user->update($validated);

        AdminLog::create([
            'admin_id' => auth()->id(),
            'action' => 'update_user',
            'target_model' => 'User',
            'target_id' => $user->id,
            'data' => $validated,
        ]);

        return response()->json($user->makeHidden(['password']));
    }

    /**
     * Delete a user.
     */
    public function destroy($id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->delete();

        AdminLog::create([
            'admin_id' => auth()->id(),
            'action' => 'delete_user',
            'target_model' => 'User',
            'target_id' => $id,
        ]);

        return response()->json(['message' => 'User deleted successfully']);
    }

    /**
     * List all admins.
     */
    public function admins(): JsonResponse
    {
        $admins = User::where('is_admin', true)->paginate(request('per_page', 10));
        $admins->getCollection()->makeHidden(['password']);

        AdminLog::create([
            'admin_id' => auth()->id(),
            'action' => 'view_admins_list',
            'target_model' => 'User',
        ]);

        return response()->json($admins);
    }

    /**
     * Authenticated user updates their own profile.
     */
    public function updateSelf(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:6',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
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

        return response()->json($user->makeHidden(['password']));
    }
}
