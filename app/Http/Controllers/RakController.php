<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Budget;
use App\Models\BudgetDetail;
use App\Http\Resources\RakResource;

class RakController extends Controller
{
    public function showRakDetailByBranch($branchId) {
        $rak = Budget::with(['branch', 'user', 'detail'])
                        ->withSum('detail', 'amount')
                        ->where('branch_id', $branchId)
                        ->orderBy('updated_at', 'desc')
                        ->get();

        return RakResource::collection($rak);
    }

    public function showRakById($id) {
        $rak = Budget::with(['branch', 'user', 'detail'])
                ->withSum('detail', 'amount')
                ->find($id);

        if (!$rak) {
            return response()->json(['message' => 'Budget not found'], 404);
        }

        return new RakResource($rak);
    }

    public function showRakSummaryByBranch($branchId) {
        $rak = Budget::with(['branch', 'user'])
                    ->withSum('detail', 'amount')
                    ->where('branch_id', $branchId)
                    ->orderBy('updated_at', 'desc')
                    ->get();
    
        return RakResource::collection($rak);
    }

    public function showRakAll() {
        $rak = Budget::with(['branch', 'user'])
                    ->withSum('detail', 'amount')
                    ->where('status', '!=', 'draf')
                    ->orderBy('updated_at', 'desc')
                    ->get();
    
        return RakResource::collection($rak);
    }

    public function updateStatus(Request $request, $id) {
        $rak = Budget::findOrFail($id);

        if (!$rak) {
            return response()->json(['message' => 'Budget not found'], 404);
        }

        $validated = $request->validate([
            'status' => 'required|string|max:24',
            'revision_note' => 'nullable|string|max:255',
        ]);

        $rak->update($validated);

        return response()->json([
            'message' => 'Status berhasil diupdate',
            'data' => new RakResource($rak)
        ], 200);
    }

    public function deleteRakByBranch($budgetId)
    {
        $deleted = BudgetDetail::where('budget_id', $budgetId)->delete();

        return response()->json([
            'message' => 'Semua rincian anggaran berhasil dihapus.',
            'deleted_count' => $deleted
        ], 200);
    }
}
