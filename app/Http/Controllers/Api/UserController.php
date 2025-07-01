<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\AdminLog;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all()->makeHidden(['password']);

        AdminLog::create([
            'admin_id' => auth()->id(),
            'action' => 'view_users_list',
            'target_model' => 'User',
            'data' => null,
        ]);

        return $users;
    }

    public function show($id)
    {
        $user = User::findOrFail($id);

        AdminLog::create([
            'admin_id' => auth()->id(),
            'action' => 'view_user',
            'target_model' => 'User',
            'target_id' => $user->id,
            'data' => null,
        ]);

        return $user->makeHidden(['password']);
    }

    public function store(Request $request)
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
            'data' => $user->only(['name', 'email', 'phone', 'address', 'is_admin']),
        ]);

        return response()->json($user->makeHidden(['password']), 201);
    }

    public function update(Request $request, $id)
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
        if ($validated['role'] === 'customer') {
            $validated['is_admin'] = false;
        } elseif (!isset($validated['is_admin'])) {
            $validated['is_admin'] = true;
        }
        if ($user->role === 'super_admin' && auth()->id() !== $user->id) {
            return response()->json(['message' => 'You cannot modify another super admin.'], 403);
        }
        
        

        $user->update($validated);

        AdminLog::create([
            'admin_id' => auth()->id(),
            'action' => 'update_user',
            'target_model' => 'User',
            'target_id' => $user->id,
            'data' => $validated,
        ]);

        return $user->makeHidden(['password']);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        AdminLog::create([
            'admin_id' => auth()->id(),
            'action' => 'delete_user',
            'target_model' => 'User',
            'target_id' => $id,
            'data' => null,
        ]);

        return response()->json(['message' => 'User deleted successfully']);
    }

    public function admins()
{
    $admins = User::where('is_admin', true)->get()->makeHidden(['password']);

    AdminLog::create([
        'admin_id' => auth()->id(),
        'action' => 'view_admins_list',
        'target_model' => 'User',
        'data' => null,
    ]);

    return response()->json($admins);
}

public function updateSelf(Request $request)
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
        'data' => $user->only(['name', 'email', 'phone', 'address', 'is_admin', 'role']),

        'admin_id' => $user->id,
        'action' => 'update_self',
        'target_model' => 'User',
        'target_id' => $user->id,
        'data' => $validated,
    ]);

    return response()->json($user->makeHidden(['password']));
}


}
