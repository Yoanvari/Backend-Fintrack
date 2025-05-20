<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;

class DashboardAdminController extends Controller
{
    //
    public function summary()
    {
        $pemasukan = Transaction::with('category')
            ->whereHas('category', function ($query) {
                $query->where('category_type', 'pemasukan');
            })
            ->sum('amount');

        $pengeluaran = Transaction::with('category')
            ->whereHas('category', function ($query) {
                $query->where('category_type', 'pengeluaran');
            })
            ->sum('amount');

        $saldo = $pemasukan - $pengeluaran;

        return response()->json([
            'pemasukan' => $pemasukan,
            'pengeluaran' => $pengeluaran,
            'saldo' => $saldo,
        ]);
    }
    public function trendChart()
    {
        // Ambil data transaksi grouped by bulan dan category_type
        $data = Transaction::with('category')
            ->selectRaw('MONTH(transaction_date) as month')
            ->selectRaw('SUM(CASE WHEN category_type = "pemasukan" THEN amount ELSE 0 END) as pemasukan')
            ->selectRaw('SUM(CASE WHEN category_type = "pengeluaran" THEN amount ELSE 0 END) as pengeluaran')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Format data untuk chart.js
        $labels = $data->pluck('month')->map(fn($m) => (string) $m)->toArray();
        $pemasukan = $data->pluck('pemasukan')->toArray();
        $pengeluaran = $data->pluck('pengeluaran')->toArray();

        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Pengeluaran',
                    'data' => $pengeluaran,
                    'backgroundColor' => '#FF3B30',
                ],
                [
                    'label' => 'Pemasukan',
                    'data' => $pemasukan,
                    'backgroundColor' => '#3A36DB',
                ],
            ],
        ]);
    }
    public function trendChartYearly()
    {
        $data = Transaction::with('category')
            ->selectRaw('YEAR(transaction_date) as year')
                ->selectRaw("SUM(CASE WHEN categories.category_type = 'pemasukan' THEN amount ELSE 0 END) as total_income")
                ->selectRaw("SUM(CASE WHEN categories.category_type = 'penngeluaran' THEN amount ELSE 0 END) as total_expense")
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->groupBy('year')
            ->orderBy('year')
            ->get();

        $result = $data->map(function ($item) {
            return [
                'year' => $item->year,
                'total' => $item->total_income - $item->total_expense,
            ];
        });

        return response()->json($result);
    }
    public function recentTransactions()
    {
        $transactions = Transaction::with('category')
            ->orderByDesc('transaction_date')
            ->take(10)
            ->get()
            ->map(function ($item) {
                return [
                    'tipe' => $item->category->category_type,
                    'jumlah' => $item->amount,
                    'tanggal' => $item->transaction_date,
                ];
            });

        return response()->json($transactions);
    }
}
