<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Budget;

class BudgetController extends Controller
{
    public function index() {
        return response()->json(Budget::all(),200);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'master_budget_id' => 'required|exists:master_budgets,id',
            'user_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $budget = Budget::create($validated);

        return response()->json($budget, 201);
    }

    public function show($id) {
        $budget = Budget::find($id);

        if (!$budget) {
            return response()->json(['message' => 'Budget not found'], 404);
        }

        return response()->json($budget, 200);
    }

    public function update(Request $request, Budget $budget) {
        $validated = $request->validate([
            'master_budget_id' => 'required|exists:master_budgets,id',
            'user_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $budget->update($validated);

        return response()->json($budget, 200);
    }

    public function destroy($id) {
        $budget = Budget::find($id);

        if (!$budget) {
            return response()->json(['message' => 'Budget not found'], 404);
        }

        $budget->delete();

        return response()->json(null, 204);
    }
}
