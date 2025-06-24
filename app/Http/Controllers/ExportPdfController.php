<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Budget;
use App\Models\BudgetDetail;
use App\Models\Category;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportPdfController extends Controller
{
    /**
     * Export comprehensive financial report
     */
    public function exportLaporanKeuanganLengkap($branchId)
    {
        // 1. Get Rekapitulasi Data
        $totalAnggaran = BudgetDetail::whereHas('budget', function ($query) use ($branchId) {
            $query->where('branch_id', $branchId)
                ->where('status', 'disetujui')
                ->whereDate('period', '<', now()->startOfMonth());
        })->sum('amount');

        $totalPemasukan = Transaction::where('branch_id', $branchId)
            ->where('is_locked', true)
            ->whereHas('category', fn ($q) => $q->where('category_type', 'pemasukan'))
            ->sum('amount');

        $totalPengeluaran = Transaction::where('branch_id', $branchId)
            ->where('is_locked', true)
            ->whereHas('category', fn ($q) => $q->where('category_type', 'pengeluaran'))
            ->sum('amount');

        $sisaAnggaran = $totalAnggaran - $totalPengeluaran;

        // 2. Get Realisasi vs Anggaran Data
        $categories = Category::where('category_type', 'pengeluaran')->get();
        $data = collect();

        foreach ($categories as $category) {
            $anggaran = BudgetDetail::where('category_id', $category->id)
                ->whereHas('budget', fn ($query) =>
                    $query->where('branch_id', $branchId)
                        ->where('status', 'disetujui')
                        ->whereDate('period', '<', now()->startOfMonth())
                )
                ->sum('amount');

            $realisasi = Transaction::where('branch_id', $branchId)
                ->where('category_id', $category->id)
                ->where('is_locked', true)
                ->whereHas('category', fn ($q) => $q->where('category_type', 'pengeluaran'))
                ->sum('amount');

            $selisih = $anggaran - $realisasi;

            $data->push((object)[
                'category_name' => $category->category_name,
                'anggaran' => $anggaran,
                'realisasi' => $realisasi,
                'selisih' => $selisih,
            ]);
        }

        // Add total row for realisasi vs anggaran
        $data->push((object)[
            'category_name' => 'TOTAL',
            'anggaran' => $data->sum('anggaran'),
            'realisasi' => $data->sum('realisasi'),
            'selisih' => $data->sum('selisih'),
            'is_total' => true,
        ]);

        // 3. Get Pemasukan Data
        $pemasukan = Transaction::with(['category'])
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.branch_id', $branchId)
            ->where('categories.category_type', 'pemasukan')
            ->where('transactions.is_locked', true)
            ->orderBy('transactions.transaction_date', 'asc')
            ->select('transactions.*')
            ->get();

        // Add total row for pemasukan
        $pemasukan->push((object)[
            'transaction_date' => null,
            'category' => (object)['category_name' => 'TOTAL PEMASUKAN'],
            'description' => null,
            'amount' => $totalPemasukan,
            'is_total' => true
        ]);

        // 4. Get Pengeluaran Data
        $pengeluaran = Transaction::with(['category'])
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.branch_id', $branchId)
            ->where('categories.category_type', 'pengeluaran')
            ->where('transactions.is_locked', true)
            ->orderBy('transactions.transaction_date', 'asc')
            ->select('transactions.*')
            ->get();

        // Add total row for pengeluaran
        $pengeluaran->push((object)[
            'transaction_date' => null,
            'category' => (object)['category_name' => 'TOTAL PENGELUARAN'],
            'description' => null,
            'amount' => $totalPengeluaran,
            'is_total' => true
        ]);

        // 5. Get Budget Data
        $budgets = Budget::with(['user', 'detail.category'])
            ->where('branch_id', $branchId)
            ->where('status', 'disetujui')
            ->whereDate('period', '<', now()->startOfMonth())
            ->orderBy('period', 'desc')
            ->get();

        // Generate PDF with all data
        $pdf = Pdf::loadView('pdf.rekapitulasi_pdf', compact(
            'totalAnggaran',
            'totalPemasukan', 
            'totalPengeluaran',
            'sisaAnggaran',
            'data',
            'pemasukan',
            'pengeluaran',
            'budgets'
        ))->setPaper('A4', 'portrait');

        return $pdf->download('laporan_rekapitulasi.pdf');
    }
}
