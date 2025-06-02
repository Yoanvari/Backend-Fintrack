<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserCollection;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('branch')
                    ->orderBy('updated_at', 'desc')
                    ->get();

        $total = $users->count();
        return (new UserCollection($users))
            ->additional([
                'meta' => [
                    'total' => $total,
                ],
            ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:super_admin,admin',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $user = User::create($validated);

        return new UserResource($user);
    }

    public function show($id)
    {
        $user = User::with('branch')->find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return new UserResource($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:6',
            'role' => 'required|in:super_admin,admin,staff',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return new UserResource($user);
    }


    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(null, 204);
    }

    public function getAdmins()
    {
        $admins = User::where('role', 'admin')
                    ->with('branch')
                    ->orderBy('updated_at', 'desc')
                    ->get();

        $total = $admins->count();
        return (new UserCollection($admins))
            ->additional([
                'meta' => [
                    'total' => $total,
                ],
            ]);
    }
}
