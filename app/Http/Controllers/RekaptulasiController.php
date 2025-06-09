<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\BudgetDetail;

class RekaptulasiController extends Controller
{
    public function index()
    {
        $rekap = Transaction::select(
                DB::raw('branches.branch_name'),
                DB::raw("DATE_FORMAT(transactions.transaction_date, '%m-%Y') as periode"),
                DB::raw("SUM(CASE WHEN categories.category_type = 'pemasukan' THEN transactions.amount ELSE 0 END) as total_pemasukan"),
                DB::raw("SUM(CASE WHEN categories.category_type = 'pengeluaran' THEN transactions.amount ELSE 0 END) as total_pengeluaran"),
                DB::raw("COUNT(*) as total_transaksi"),
                DB::raw("COUNT(CASE WHEN categories.category_type = 'pemasukan' THEN 1 END) as total_transaksi_pemasukan"),
                DB::raw("COUNT(CASE WHEN categories.category_type = 'pengeluaran' THEN 1 END) as total_transaksi_pengeluaran"),
                DB::raw("COUNT(CASE WHEN transactions.is_locked = 1 THEN 1 END) as total_locked"),
                DB::raw("branches.id as branch_id"),
                DB::raw("DATE_FORMAT(transactions.transaction_date, '%Y-%m') as raw_periode")
            )
            ->join('branches', 'transactions.branch_id', '=', 'branches.id')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->groupBy(
                'branches.id',
                'branches.branch_name',
                DB::raw("DATE_FORMAT(transactions.transaction_date, '%m-%Y')"),
                DB::raw("DATE_FORMAT(transactions.transaction_date, '%Y-%m')")
            )
            ->orderBy('periode', 'desc')
            ->get()
            ->map(function ($item) {
                $total_anggaran = BudgetDetail::join('budgets', 'budget_details.budget_id', '=', 'budgets.id')
                    ->where('budgets.branch_id', $item->branch_id)
                    ->whereRaw("DATE_FORMAT(budgets.period, '%Y-%m') = ?", [$item->raw_periode])
                    ->sum('budget_details.amount');

                return [
                    'branch_name' => $item->branch_name,
                    'periode' => $item->periode,
                    'total_pemasukan' => $item->total_pemasukan,
                    'total_pengeluaran' => $item->total_pengeluaran,
                    'total_transaksi' => $item->total_transaksi,
                    'total_transaksi_pemasukan' => $item->total_transaksi_pemasukan,
                    'total_transaksi_pengeluaran' => $item->total_transaksi_pengeluaran,
                    'total_locked' => $item->total_locked,
                    'total_anggaran' => $total_anggaran,
                ];
            });

        return response()->json([
            'data' => $rekap
        ]);
    }

    public function showByBranch($branch_id)
    {
        $rekap = Transaction::select(
                DB::raw('branches.branch_name'),
                DB::raw("DATE_FORMAT(transactions.transaction_date, '%m-%Y') as periode"),
                DB::raw("SUM(CASE WHEN categories.category_type = 'pemasukan' THEN transactions.amount ELSE 0 END) as total_pemasukan"),
                DB::raw("SUM(CASE WHEN categories.category_type = 'pengeluaran' THEN transactions.amount ELSE 0 END) as total_pengeluaran"),
                DB::raw("COUNT(*) as total_transaksi"),
                DB::raw("COUNT(CASE WHEN categories.category_type = 'pemasukan' THEN 1 END) as total_transaksi_pemasukan"),
                DB::raw("COUNT(CASE WHEN categories.category_type = 'pengeluaran' THEN 1 END) as total_transaksi_pengeluaran"),
                DB::raw("COUNT(CASE WHEN transactions.is_locked = 1 THEN 1 END) as total_locked"),
                DB::raw("branches.id as branch_id"),
                DB::raw("DATE_FORMAT(transactions.transaction_date, '%Y-%m') as raw_periode")
            )
            ->join('branches', 'transactions.branch_id', '=', 'branches.id')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('branches.id', $branch_id)
            ->groupBy(
                'branches.id',
                'branches.branch_name',
                DB::raw("DATE_FORMAT(transactions.transaction_date, '%m-%Y')"),
                DB::raw("DATE_FORMAT(transactions.transaction_date, '%Y-%m')")
            )
            ->orderBy('periode', 'desc')
            ->get()
            ->map(function ($item) {
                $total_anggaran = BudgetDetail::join('budgets', 'budget_details.budget_id', '=', 'budgets.id')
                    ->where('budgets.branch_id', $item->branch_id)
                    ->whereRaw("DATE_FORMAT(budgets.period, '%Y-%m') = ?", [$item->raw_periode])
                    ->sum('budget_details.amount');

                return [
                    'branch_name' => $item->branch_name,
                    'periode' => $item->periode,
                    'total_pemasukan' => $item->total_pemasukan,
                    'total_pengeluaran' => $item->total_pengeluaran,
                    'total_transaksi' => $item->total_transaksi,
                    'total_transaksi_pemasukan' => $item->total_transaksi_pemasukan,
                    'total_transaksi_pengeluaran' => $item->total_transaksi_pengeluaran,
                    'total_locked' => $item->total_locked,
                    'total_anggaran' => $total_anggaran,
                ];
            });

        return response()->json([
            'data' => $rekap
        ]);
    }
}