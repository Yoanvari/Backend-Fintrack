<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MasterBudgetController extends Controller
{
    public function index() {
        return response()->json(MasterBudget::all(),200);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $master = MasterBudget::create($validated);

        return response()->json($master, 201);
    }

    public function show(MasterBudget $master) {
        return response()->json($master, 200);
    }

    public function update(Request $request, MasterBudget $master) {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $master->update($validated);

        return response()->json($master, 200);
    }

    public function destroy(MasterBudget $master) {
        $master->delete();

        return response()->json(null, 204);
    }
}
