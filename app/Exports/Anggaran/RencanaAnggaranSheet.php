<?php

namespace App\Exports\Anggaran;

use App\Models\Budget;
use App\Models\BudgetDetail;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RencanaAnggaranSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    protected $branchId;
    protected $budgets;
    protected $totalRows = 0;

    public function __construct($branchId)
    {
        $this->branchId = $branchId;
        $this->budgets = \App\Models\Budget::with(['user', 'detail.category'])
            ->where('branch_id', $branchId)
            ->where('status', 'disetujui')
            ->orderBy('period', 'desc')
            ->get();
    }

    public function collection()
    {
        $collection = collect();
        $currentRow = 0;

        foreach ($this->budgets as $budget) {
            // Add budget header info (3 rows)
            $collection->push((object)[
                'type' => 'header',
                'field' => 'periode',
                'value' => 'Periode: ' . $budget->period->format('F Y'),
                'row_number' => ++$currentRow
            ]);
            
            $collection->push((object)[
                'type' => 'header',
                'field' => 'diajukan_oleh',
                'value' => 'Diajukan oleh: ' . $budget->user->name,
                'row_number' => ++$currentRow
            ]);
            
            $collection->push((object)[
                'type' => 'header',
                'field' => 'waktu_pengajuan',
                'value' => 'Waktu Pengajuan: ' . $budget->submission_date->format('d/m/Y H:i'),
                'row_number' => ++$currentRow
            ]);

            // Add empty row
            $collection->push((object)[
                'type' => 'empty',
                'row_number' => ++$currentRow
            ]);

            // Add table header
            $collection->push((object)[
                'type' => 'table_header',
                'row_number' => ++$currentRow
            ]);

            // Add budget details
            $no = 1;
            foreach ($budget->detail as $detail) {
                $collection->push((object)[
                    'type' => 'detail',
                    'no' => $no++,
                    'category_name' => $detail->category->category_name,
                    'description' => $detail->description,
                    'amount' => $detail->amount,
                    'row_number' => ++$currentRow
                ]);
            }

            // Add total row
            $total = $budget->detail->sum('amount');
            $collection->push((object)[
                'type' => 'total',
                'total' => $total,
                'row_number' => ++$currentRow
            ]);

            // Add separator
            $collection->push((object)[
                'type' => 'separator',
                'row_number' => ++$currentRow
            ]);
            $collection->push((object)[
                'type' => 'separator',
                'row_number' => ++$currentRow
            ]);
        }

        $this->totalRows = $currentRow;
        return $collection;
    }

    public function headings(): array
    {
        return [];
    }

    public function map($item): array
    {
        switch ($item->type) {
            case 'header':
                return [$item->value, '', '', ''];
            
            case 'empty':
                return ['', '', '', ''];
            
            case 'table_header':
                return ['No', 'Kategori', 'Deskripsi', 'Jumlah'];
            
            case 'detail':
                return [
                    $item->no,
                    $item->category_name,
                    $item->description,
                    number_format($item->amount, 0, ',', '.')
                ];
            
            case 'total':
                return [
                    '',
                    '',
                    'TOTAL',
                    number_format($item->total, 0, ',', '.')
                ];
            
            case 'separator':
                return ['', '', '', ''];
            
            default:
                return ['', '', '', ''];
        }
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [];
        $currentRow = 1;

        foreach ($this->budgets as $budget) {
            // Header styles (3 rows)
            for ($i = 0; $i < 3; $i++) {
                $styles[$currentRow] = [
                    'font' => ['bold' => true, 'size' => 12],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E3F2FD']
                    ]
                ];
                $currentRow++;
            }

            // Skip empty row
            $currentRow++;

            // Table header style
            $styles[$currentRow] = [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0']
                ]
            ];
            $currentRow++;

            // Skip detail rows
            $currentRow += count($budget->detail);

            // Total row style
            $styles[$currentRow] = [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'C8E6C9']
                ]
            ];
            $currentRow++;

            $currentRow += 2;
        }

        return $styles;
    }

    public function title(): string
    {
        return 'Rencana Anggaran';
    }
}
