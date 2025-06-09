<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Budget;
use App\Models\BudgetDetail;
use App\Http\Resources\BudgetResource;
use App\Http\Resources\BudgetCollection;

class BudgetController extends Controller
{
    public function index() {
        $budgets = Budget::with(['branch', 'user'])
                        ->orderBy('updated_at', 'desc')
                        ->get();

        $total = $budgets->count();
        return (new BudgetCollection($budgets))
            ->additional([
                'meta' => [
                    'total' => $total
                ],
            ]);
    }

    public function showByBranch($branchId) {
        $budget = Budget::with(['branch', 'user'])
                        ->where('branch_id', $branchId)
                        ->orderBy('updated_at', 'desc')
                        ->get();

        $total = $budget->count();
        return (new BudgetCollection($budget))
            ->additional([
                'meta' => [
                    'total' => $total
                ],
            ]);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'user_id' => 'required|exists:users,id',
            'period' => 'required|date',
            'submission_date' => 'required|date',
            'status' => 'required|string|max:24',
            'revision_note' => 'nullable|string',
        ]);

        $budget = Budget::create($validated);

        return new BudgetResource($budget);
    }

    public function show($id) {
        $budget = Budget::find($id);

        if (!$budget) {
            return response()->json(['message' => 'Budget not found'], 404);
        }

        return new BudgetResource($budget);
    }

    public function update(Request $request, Budget $budget) {
        $validated = $request->validate([
            'branch_id' => 'sometimes|exists:branches,id',
            'user_id' => 'sometimes|exists:users,id',
            'period' => 'required|date',
            'submission_date' => 'required|date',
            'status' => 'required|string|max:24',
            'revision_note' => 'nullable|string',
        ]);

        $budget->update($validated);

        return new BudgetResource($budget);
    }

    public function destroy($id) {
        $budget = Budget::find($id);

        if (!$budget) {
            return response()->json(['message' => 'Budget not found'], 404);
        }

        $budget->delete();

        return response()->json([
            'message' => 'Budget dan semua detailnya berhasil dihapus.'
        ]);
    }
}
