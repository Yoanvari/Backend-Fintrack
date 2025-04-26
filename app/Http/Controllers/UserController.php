<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index() {
        return response()->json(User::all(), 200);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255',
            'password' => 'require|string|max:255',
            'role' => 'required|in:super_admin,admin',
        ]);

        $user = User::crate($validated);

        return response()->json($user, 201);
    }

    public function show(User $user) {
        return response()->json($user, 200);
    }

    public function update(Request $request, User $user) {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255',
            'password' => 'require|string|max:255',
            'role' => 'required|in:super_admin,admin',
        ]);

        $user->update($validated);

        return response()->json($user, 200);
    }

    public function destroy(User $user) {
        $user->delete();

        return response()->json(null, 204);
    }
}
