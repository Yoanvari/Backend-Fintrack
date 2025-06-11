<?php

namespace App\Exports\Rekapitulasi;

use App\Models\Transaction;
use App\Models\BudgetDetail;
use App\Models\Category;
use Maatwebsite\Excel\Concerns\{FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapitulasiSheet implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    protected $branchId;

    public function __construct($branchId)
    {
        $this->branchId = $branchId;
    }

    public function array(): array
    {
        $totalAnggaran = BudgetDetail::whereHas('budget', function ($query) {
                $query->where('branch_id', $this->branchId)
                    ->where('status', 'disetujui')
                    ->whereDate('period', '<', now()->startOfMonth());
            })
            ->sum('amount');

        $totalPemasukan = Transaction::where('branch_id', $this->branchId)
            ->where('is_locked', true)
            ->whereHas('category', function ($q) {
                $q->where('category_type', 'pemasukan');
            })
            ->sum('amount');

        $totalPengeluaran = Transaction::where('branch_id', $this->branchId)
            ->where('is_locked', true)
            ->whereHas('category', function ($q) {
                $q->where('category_type', 'pengeluaran');
            })
            ->sum('amount');

        $sisaAnggaran = $totalAnggaran - $totalPengeluaran;

        return [
            ['Total Anggaran', $totalAnggaran],
            ['Total Pemasukan', $totalPemasukan],
            ['Total Pengeluaran', $totalPengeluaran],
            ['Sisa Anggaran', $sisaAnggaran],
        ];
    }

    public function headings(): array
    {
        return ['Keterangan', 'Jumlah'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0']
                ]
            ],
        ];
    }

    public function title(): string
    {
        return 'Rekapitulasi';
    }
}
