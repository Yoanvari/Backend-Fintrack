<?php

namespace App\Exports\Anggaran;

use App\Models\Category;
use App\Models\BudgetDetail;
use App\Models\Transaction;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RealisasiVsAnggaranSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    protected $branchId;
    protected $data;
    protected $rowCount = 0;

    public function __construct($branchId)
    {
        $this->branchId = $branchId;
    }

    public function collection()
    {
        // Ambil semua kategori pengeluaran
        $categories = Category::where('category_type', 'pengeluaran')->get();

        $result = collect();

        foreach ($categories as $category) {
            $anggaran = BudgetDetail::where('category_id', $category->id)
                ->whereHas('budget', function ($query) {
                    $query->where('branch_id', $this->branchId)
                        ->where('status', 'disetujui')
                        ->whereDate('period', '<', now()->startOfMonth());
                })
                ->sum('amount');

            $realisasi = Transaction::where('branch_id', $this->branchId)
                ->where('category_id', $category->id)
                ->where('is_locked', true)
                ->whereHas('category', fn ($q) => $q->where('category_type', 'pengeluaran'))
                ->sum('amount');

            $selisih = $anggaran - $realisasi;

            $result->push((object)[
                'category_name' => $category->category_name,
                'anggaran' => $anggaran,
                'realisasi' => $realisasi,
                'selisih' => $selisih,
            ]);
        }

        $this->rowCount = $result->count();

        $result->push((object)[
            'category_name' => 'TOTAL',
            'anggaran' => $result->sum('anggaran'),
            'realisasi' => $result->sum('realisasi'),
            'selisih' => $result->sum('selisih'),
            'is_total' => true,
        ]);

        return $result;
    }

    public function headings(): array
    {
        return ['Kategori', 'Anggaran', 'Realisasi', 'Selisih'];
    }

    public function map($row): array
    {
        return [
            $row->category_name,
            number_format($row->anggaran, 0, ',', '.'),
            number_format($row->realisasi, 0, ',', '.'),
            number_format($row->selisih, 0, ',', '.'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $totalRow = $this->rowCount + 2; // +1 header, +1 1-indexed

        return [
            1 => [ // header
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0']
                ]
            ],
            $totalRow => [ // total row
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D1FAE5'] // hijau muda
                ]
            ]
        ];
    }

    public function title(): string
    {
        return 'Realisasi vs Anggaran';
    }
}
