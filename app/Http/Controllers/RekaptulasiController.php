<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Exports\RekaptulasiExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\BudgetDetail;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Budget;
use App\Models\Category;
use Barryvdh\DomPDF\Facade\Pdf;

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

    /**
     * Export transactions to Excel by branch ID from URL parameter
     */
    public function exportExcelByBranch($branchId)
    {
        // Validasi branch exists
        $branch = \App\Models\Branch::find($branchId);
        if (!$branch) {
            return response()->json([
                'success' => false,
                'message' => 'Branch tidak ditemukan'
            ], 404);
        }

        try {
            $fileName = 'Rekapitulasi_' . str_replace(' ', '_', $branch->branch_name) . '_' . date('Y_m_d_H_i_s') . '.xlsx';
            
            return Excel::download(new RekaptulasiExport($branchId), $fileName, 'Xlsx');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengexport data: ' . $e->getMessage()
            ], 500);
        }
    }

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