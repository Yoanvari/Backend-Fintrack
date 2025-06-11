<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\TransactionCollection;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function index() {
        return response()->json(Transaction::all(),200);
    }

    public function showDetail() {
        $transaction = Transaction::with(['user', 'branch', 'category'])
                        ->orderBy('transaction_date', 'desc')
                        ->get();

        $total = $transaction->count();
        $todayCount = Transaction::whereDate('transaction_date', Carbon::now('Asia/Jakarta')->toDateString())->count();
        $lockedCount = Transaction::where('is_locked', true)->count();
        return (new TransactionCollection($transaction))
            ->additional([
                'meta' => [
                    'total' => $total,
                    'today_count' => $todayCount,
                    'locked_count' => $lockedCount,
                ],
            ]);
    }

    public function showByBranch($branchId) {
        $transaction = Transaction::with(['user', 'branch', 'category'])
                        ->where('branch_id', $branchId)
                        ->orderBy('transaction_date', 'desc')
                        ->get();

        $todayCount = Transaction::whereDate('transaction_date', Carbon::now('Asia/Jakarta')->toDateString())->count();
        $lockedCount = Transaction::where('is_locked', true)->count();
        return (new TransactionCollection($transaction))
            ->additional([
                'meta' => [
                    'total' => $transaction->count(),
                    'today_count' => $todayCount,
                    'locked_count' => $lockedCount,
                ],
            ]);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'branch_id' => 'required|exists:branches,id',
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'transaction_date' => 'required|date',
        ]);

        $transaction = Transaction::create($validated);

        return new TransactionResource($transaction);
    }

    public function show($id) {
        $transaction = Transaction::with(['user', 'branch', 'category'])->find($id);
        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        return new TransactionResource($transaction);
    }

    public function update(Request $request, $id){
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        $validated = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'branch_id' => 'sometimes|exists:branches,id',
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'transaction_date' => 'required|date',
        ]);

        $transaction->update($validated);

        return new TransactionResource($transaction);
    }

    public function destroy($id) {
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        $transaction->delete();

        return response()->json(null, 204);
    }

    public function lock($id) {
        $transaction = Transaction::findOrFail($id);
      
        if ($transaction->is_locked) {
            $transaction->is_locked = false;
            $transaction->save();
            return response()->json(['message' => 'Transaksi berhasil dibuka', 'data' => $transaction]);
        }
      
        $transaction->is_locked = true;
        $transaction->save();
      
        return response()->json(['message' => 'Transaksi berhasil dikunci', 'data' => $transaction]);
    }
    
    public function showPos() {
        $transaction = Transaction::with(['user', 'branch', 'category'])
                        ->where('category_id', 3)
                        ->orderBy('transaction_date', 'desc')
                        ->get();

        $total = $transaction->count();
        $totalAmount = $transaction->sum('amount');
        return (new TransactionCollection($transaction))
            ->additional([
                'meta' => [
                    'total' => $total,
                    'total_amount' => $totalAmount,
                ],
            ]);
    }
}
