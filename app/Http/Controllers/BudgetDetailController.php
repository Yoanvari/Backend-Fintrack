<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BudgetDetail;

class BudgetDetailController extends Controller
{
    public function index() {
        return response()->json(BudgetDetail::all(),200);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'budget_id' => 'required|exists:budgets,id',
            'transaction_id' => 'required|exists:transactions,id',
            'amount' => 'required|numeric|min:0',
        ]);

        $detail = BudgetDetail::create($validated);

        return response()->json($detail, 201);
    }

    public function show($id) {
        $detail = BudgetDetail::find($id);

        if (!$detail) {
            return response()->json(['message' => 'Budget Detail not found'], 404);
        }

        return response()->json($detail, 200);
    }

    public function update(Request $request, BudgetDetail $detail) {
        $validated = $request->validate([
            'budget_id' => 'required|exists:budgets,id',
            'transaction_id' => 'required|exists:transactions,id',
            'amount' => 'required|numeric|min:0',
        ]);

        $detail->update($validated);

        return response()->json($detail, 200);
    }

    public function destroy($id) {
        $detail = BudgetDetail::find($id);

        if (!$detail) {
            return response()->json(['message' => 'Budget Detail not found'], 404);
        }

        $detail->delete();

        return response()->json(null, 204);
    }
}
