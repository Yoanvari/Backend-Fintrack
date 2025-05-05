<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PosTransaction;

class PosTransactionController extends Controller
{
    public function index() {
        return response()->json(PosTransaction::all(),200);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'reservation_id' => 'require|string|max:255',
            'branch_id' => 'required|exists:branches,id',
            'total_amount' => 'required|numeric|min:0',
            'payment_status' => 'required|boolean',
        ]);

        $pos = PosTransaction::create($validated);

        return response()->json($pos, 201);
    }

    public function show($id) {
        $pos = PosTransaction::find($id);

        if (!$pos) {
            return response()->json(['message' => 'POS Transaction not found'], 404);
        }

        return response()->json($pos, 200);
    }

    public function update(Request $request, PosTransaction $pos) {
        $validated = $request->validate([
            'reservation_id' => 'require|string|max:255',
            'branch_id' => 'required|exists:branches,id',
            'total_amount' => 'required|numeric|min:0',
            'payment_status' => 'required|boolean',
        ]);

        $pos->update($validated);

        return response()->json($pos, 200);
    }

    public function destroy(PosTransaction $pos) {
        $pos->delete();

        return response()->json(null, 204);
    }
}
