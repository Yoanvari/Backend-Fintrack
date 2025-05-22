<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Transaction;

class DashboardSuperAdminController extends Controller
{
    public function summary()
    {
        // Ambil semua cabang beserta transaksi dan kategorinya
        $branches = Branch::with(['transactions.category'])->get();

        // Hitung total pemasukan, pengeluaran, dan saldo keseluruhan
        $totalPemasukan = 0;
        $totalPengeluaran = 0;

        // Proses data tiap cabang
        $branchData = $branches->map(function ($branch) use (&$totalPemasukan, &$totalPengeluaran) {
            $pemasukan = $branch->transactions
                ->where('category.category_type', 'pemasukan')
                ->sum('amount');

            $pengeluaran = $branch->transactions
                ->where('category.category_type', 'pengeluaran')
                ->sum('amount');

            $saldo = $pemasukan - $pengeluaran;

            // Akumulasi total
            $totalPemasukan += $pemasukan;
            $totalPengeluaran += $pengeluaran;

            return [
                'branch_code' => $branch->branch_code,
                'branch_name' => $branch->branch_name,
                'branch_address' => $branch->branch_address,
                'pemasukan' => $pemasukan,
                'pengeluaran' => $pengeluaran,
                'saldo' => $saldo,
            ];
        });

        return response()->json([
            'summary' => [
                'total_pemasukan' => $totalPemasukan,
                'total_pengeluaran' => $totalPengeluaran,
                'total_saldo' => $totalPemasukan - $totalPengeluaran,
                'jumlah_cabang' => $branches->count(),
            ],
            'branches' => $branchData,
        ]);
    }
    public function trendLine()
    {
        // Ambil total pemasukan per tahun
        $income = Transaction::selectRaw("YEAR(transaction_date) as year")
            ->selectRaw("SUM(amount) as total")
            ->whereHas('category', fn($q) => $q->where('category_type', 'pemasukan'))
            ->groupBy('year')
            ->orderBy('year')
            ->get()
            ->pluck('total', 'year');

        // Ambil total pengeluaran per tahun
        $expense = Transaction::selectRaw("YEAR(transaction_date) as year")
            ->selectRaw("SUM(amount) as total")
            ->whereHas('category', fn($q) => $q->where('category_type', 'pengeluaran'))
            ->groupBy('year')
            ->orderBy('year')
            ->get()
            ->pluck('total', 'year');

        // Gabungkan semua tahun dari pemasukan & pengeluaran
        $years = collect($income->keys())
            ->merge($expense->keys())
            ->unique()
            ->sort()
            ->values();

        // Hitung total keuangan per tahun (income - expense)
        $totals = $years->map(function ($year) use ($income, $expense) {
            $i = $income[$year] ?? 0;
            $e = $expense[$year] ?? 0;
            return $i - $e;
        });

        return response()->json([
            'labels' => $years,
            'datasets' => [
                [
                    'label' => 'Total Keuangan',
                    'data' => $totals,
                    'borderColor' => 'rgb(16, 185, 129)', // hijau
                    'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
                ]
            ]
        ]);
    }

    public function trendBar()
    {
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        // Ambil pemasukan bulanan
        $income = Transaction::selectRaw("DATE_FORMAT(transaction_date, '%b') as month")
            ->selectRaw("SUM(amount) as total")
            ->whereHas('category', fn($q) => $q->where('category_type', 'pemasukan'))
            ->groupBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();

        // Ambil pengeluaran bulanan
        $expense = Transaction::selectRaw("DATE_FORMAT(transaction_date, '%b') as month")
            ->selectRaw("SUM(amount) as total")
            ->whereHas('category', fn($q) => $q->where('category_type', 'pengeluaran'))
            ->groupBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();

        // Sesuaikan urutan & isian data
        $incomeData = array_map(fn($m) => (float) ($income[$m] ?? 0), $months);
        $expenseData = array_map(fn($m) => (float) ($expense[$m] ?? 0), $months);

        return response()->json([
            'labels' => $months,
            'datasets' => [
                [
                    'label' => 'Pemasukan',
                    'data' => $incomeData,
                    'backgroundColor' => 'rgba(37, 99, 235, 0.5)',
                    'borderColor' => 'rgb(37, 99, 235)',
                ],
                [
                    'label' => 'Pengeluaran',
                    'data' => $expenseData,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.5)',
                    'borderColor' => 'rgb(239, 68, 68)',
                ]
            ]
        ]);
    }
}
