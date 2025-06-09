<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BudgetDetail;
use App\Http\Resources\BudgetDetailResource;
use App\Http\Resources\BudgetDetailCollection;

class BudgetDetailController extends Controller
{
    public function index() {
        $details = BudgetDetail::with(['budget', 'category'])
                        ->orderBy('updated_at', 'desc')
                        ->get();

        $total = $details->count();
        return (new BudgetDetailCollection($details))
            ->additional([
                'meta' => [
                    'total' => $total
                ],
            ]);
    }

    public function showByBudget($budgetId) {
        $detail = BudgetDetail::with(['budget', 'category'])
                        ->where('budget_id', $budgetId)
                        ->orderBy('updated_at', 'desc')
                        ->get();

        $total = $detail->count();
        $amount = $detail->sum('amount');
        return (new BudgetDetailCollection($detail))
            ->additional([
                'meta' => [
                    'total' => $total,
                    'total_amount' => $amount,
                ],
            ]);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'budget_id' => 'required|exists:budgets,id',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $detail = BudgetDetail::create($validated);

        return new BudgetDetailResource($detail);
    }

    public function show($id) {
        $detail = BudgetDetail::find($id);

        if (!$detail) {
            return response()->json(['message' => 'Budget Detail not found'], 404);
        }

        return new BudgetDetailResource($detail);
    }

    public function update(Request $request, BudgetDetail $detail) {
        $validated = $request->validate([
            'budget_id' => 'required|exists:budgets,id',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $detail->update($validated);

        return new BudgetDetailResource($detail);
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
