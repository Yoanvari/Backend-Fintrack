<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index() {
        return response()->json(Branch::all(),200);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'branch_code' => 'required|string|max:255',
            'branch_name' => 'required|string|max:255',
            'branch_address' => 'required|string|max:255',
            
        ]);

        $branch = Branch::create($validated);

        return response()->json($branch, 201);
    }

    public function show(Branch $branch) {
        return response()->json($branch, 200);
    }

    public function update(Request $request, Branch $branch) {
        $validated = $request->validate([
            'branch_code' => 'required|string|max:255',
            'branch_name' => 'required|string|max:255',
            'branch_address' => 'required|string|max:255',
        ]);

        $branch->update($validated);

        return response()->json($branch, 200);
    }

    public function destroy(Branch $branch) {
        $branch->delete();

        return response()->json(null, 204);
    }

}
