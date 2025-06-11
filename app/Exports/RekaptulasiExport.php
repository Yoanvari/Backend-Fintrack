<?php

namespace App\Exports;

use App\Models\Transaction;
use App\Exports\Transaction\PemasukanSheet;
use App\Exports\Transaction\PengeluaranSheet;
use App\Exports\Anggaran\RencanaAnggaranSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RekaptulasiExport implements WithMultipleSheets
{
    protected $branchId;

    public function __construct($branchId)
    {
        $this->branchId = $branchId;
    }

    public function sheets(): array
    {
        return [
            new PemasukanSheet($this->branchId),
            new PengeluaranSheet($this->branchId),
            new RencanaAnggaranSheet($this->branchId),
        ];
    }
}

// class PemasukanSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
// {
//     protected $branchId;
//     protected $rowCount = 0;

//     public function __construct($branchId)
//     {
//         $this->branchId = $branchId;
//     }

//     public function collection()
//     {
//         $transactions = Transaction::with(['category', 'branch'])
//             ->join('categories', 'transactions.category_id', '=', 'categories.id')
//             ->where('transactions.branch_id', $this->branchId)
//             ->where('categories.category_type', 'pemasukan')
//             ->orderBy('transactions.transaction_date', 'asc')
//             ->get();

//         $this->rowCount = $transactions->count();
        
//         // Add total row
//         $total = $transactions->sum('amount');
//         $transactions->push((object)[
//             'id' => null,
//             'transaction_date' => null,
//             'category' => (object)['category_name' => 'TOTAL PEMASUKAN'],
//             'description' => null,
//             'amount' => $total,
//             'is_total' => true
//         ]);

//         return $transactions;
//     }

//     public function headings(): array
//     {
//         return [
//             'No',
//             'Tanggal',
//             'Kategori',
//             'Deskripsi',
//             'Jumlah'
//         ];
//     }

//     public function map($transaction): array
//     {
//         static $counter = 0;
        
//         if (isset($transaction->is_total) && $transaction->is_total) {
//             return [
//                 '',
//                 '',
//                 $transaction->category->category_name,
//                 '',
//                 number_format($transaction->amount, 0, ',', '.')
//             ];
//         }

//         $counter++;
//         return [
//             $counter,
//             $transaction->transaction_date ? $transaction->transaction_date->format('d/m/Y') : '',
//             $transaction->category->category_name ?? '',
//             $transaction->description,
//             number_format($transaction->amount, 0, ',', '.')
//         ];
//     }

//     public function styles(Worksheet $sheet)
//     {
//         $totalRow = $this->rowCount + 2; // +1 for header, +1 for 0-index
        
//         return [
//             // Header style
//             1 => [
//                 'font' => ['bold' => true],
//                 'fill' => [
//                     'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
//                     'startColor' => ['rgb' => 'E2E8F0']
//                 ]
//             ],
//             // Total row style
//             $totalRow => [
//                 'font' => ['bold' => true],
//                 'fill' => [
//                     'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
//                     'startColor' => ['rgb' => 'FEF3C7']
//                 ]
//             ]
//         ];
//     }
// }

// class PengeluaranSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
// {
//     protected $branchId;
//     protected $rowCount = 0;

//     public function __construct($branchId)
//     {
//         $this->branchId = $branchId;
//     }

//     public function collection()
//     {
//         $transactions = Transaction::with(['category', 'branch'])
//             ->join('categories', 'transactions.category_id', '=', 'categories.id')
//             ->where('transactions.branch_id', $this->branchId)
//             ->where('categories.category_type', 'pengeluaran')
//             ->orderBy('transactions.transaction_date', 'asc')
//             ->get();

//         $this->rowCount = $transactions->count();
        
//         // Add total row
//         $total = $transactions->sum('amount');
//         $transactions->push((object)[
//             'id' => null,
//             'transaction_date' => null,
//             'category' => (object)['category_name' => 'TOTAL PENGELUARAN'],
//             'description' => null,
//             'amount' => $total,
//             'is_total' => true
//         ]);

//         return $transactions;
//     }

//     public function headings(): array
//     {
//         return [
//             'No',
//             'Tanggal',
//             'Kategori',
//             'Deskripsi',
//             'Jumlah'
//         ];
//     }

//     public function map($transaction): array
//     {
//         static $counter = 0;
        
//         if (isset($transaction->is_total) && $transaction->is_total) {
//             return [
//                 '',
//                 '',
//                 $transaction->category->category_name,
//                 '',
//                 number_format($transaction->amount, 0, ',', '.')
//             ];
//         }

//         $counter++;
//         return [
//             $counter,
//             $transaction->transaction_date ? $transaction->transaction_date->format('d/m/Y') : '',
//             $transaction->category->category_name ?? '',
//             $transaction->description,
//             number_format($transaction->amount, 0, ',', '.')
//         ];
//     }

//     public function styles(Worksheet $sheet)
//     {
//         $totalRow = $this->rowCount + 2; // +1 for header, +1 for 0-index
        
//         return [
//             // Header style
//             1 => [
//                 'font' => ['bold' => true],
//                 'fill' => [
//                     'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
//                     'startColor' => ['rgb' => 'E2E8F0']
//                 ]
//             ],
//             // Total row style
//             $totalRow => [
//                 'font' => ['bold' => true],
//                 'fill' => [
//                     'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
//                     'startColor' => ['rgb' => 'FEE2E2']
//                 ]
//             ]
//         ];
//     }
// }

// class RencanaAnggaranSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
// {
//     protected $branchId;
//     protected $budgets;
//     protected $totalRows = 0;

//     public function __construct($branchId)
//     {
//         $this->branchId = $branchId;
//         $this->budgets = \App\Models\Budget::with(['user', 'detail.category'])
//             ->where('branch_id', $branchId)
//             ->where('status', 'disetujui')
//             ->orderBy('period', 'desc')
//             ->get();
//     }

//     public function collection()
//     {
//         $collection = collect();
//         $currentRow = 0;

//         foreach ($this->budgets as $budget) {
//             // Add budget header info (3 rows)
//             $collection->push((object)[
//                 'type' => 'header',
//                 'field' => 'periode',
//                 'value' => 'Periode: ' . $budget->period->format('F Y'),
//                 'row_number' => ++$currentRow
//             ]);
            
//             $collection->push((object)[
//                 'type' => 'header',
//                 'field' => 'diajukan_oleh',
//                 'value' => 'Diajukan oleh: ' . $budget->user->name,
//                 'row_number' => ++$currentRow
//             ]);
            
//             $collection->push((object)[
//                 'type' => 'header',
//                 'field' => 'waktu_pengajuan',
//                 'value' => 'Waktu Pengajuan: ' . $budget->submission_date->format('d/m/Y H:i'),
//                 'row_number' => ++$currentRow
//             ]);

//             // Add empty row
//             $collection->push((object)[
//                 'type' => 'empty',
//                 'row_number' => ++$currentRow
//             ]);

//             // Add table header
//             $collection->push((object)[
//                 'type' => 'table_header',
//                 'row_number' => ++$currentRow
//             ]);

//             // Add budget details
//             $no = 1;
//             foreach ($budget->detail as $detail) {
//                 $collection->push((object)[
//                     'type' => 'detail',
//                     'no' => $no++,
//                     'category_name' => $detail->category->category_name,
//                     'description' => $detail->description,
//                     'amount' => $detail->amount,
//                     'row_number' => ++$currentRow
//                 ]);
//             }

//             // Add total row
//             $total = $budget->detail->sum('amount');
//             $collection->push((object)[
//                 'type' => 'total',
//                 'total' => $total,
//                 'row_number' => ++$currentRow
//             ]);

//             // Add separator
//             $collection->push((object)[
//                 'type' => 'separator',
//                 'row_number' => ++$currentRow
//             ]);
//             $collection->push((object)[
//                 'type' => 'separator',
//                 'row_number' => ++$currentRow
//             ]);
//         }

//         $this->totalRows = $currentRow;
//         return $collection;
//     }

//     public function headings(): array
//     {
//         return [];
//     }

//     public function map($item): array
//     {
//         switch ($item->type) {
//             case 'header':
//                 return [$item->value, '', '', ''];
            
//             case 'empty':
//                 return ['', '', '', ''];
            
//             case 'table_header':
//                 return ['No', 'Kategori', 'Deskripsi', 'Jumlah'];
            
//             case 'detail':
//                 return [
//                     $item->no,
//                     $item->category_name,
//                     $item->description,
//                     number_format($item->amount, 0, ',', '.')
//                 ];
            
//             case 'total':
//                 return [
//                     '',
//                     '',
//                     'TOTAL',
//                     number_format($item->total, 0, ',', '.')
//                 ];
            
//             case 'separator':
//                 return ['', '', '', ''];
            
//             default:
//                 return ['', '', '', ''];
//         }
//     }

//     public function styles(Worksheet $sheet)
//     {
//         $styles = [];
//         $currentRow = 1;

//         foreach ($this->budgets as $budget) {
//             // Header styles (3 rows)
//             for ($i = 0; $i < 3; $i++) {
//                 $styles[$currentRow] = [
//                     'font' => ['bold' => true, 'size' => 12],
//                     'fill' => [
//                         'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
//                         'startColor' => ['rgb' => 'E3F2FD']
//                     ]
//                 ];
//                 $currentRow++;
//             }

//             // Skip empty row
//             $currentRow++;

//             // Table header style
//             $styles[$currentRow] = [
//                 'font' => ['bold' => true],
//                 'fill' => [
//                     'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
//                     'startColor' => ['rgb' => 'E2E8F0']
//                 ]
//             ];
//             $currentRow++;

//             // Skip detail rows
//             $currentRow += count($budget->detail);

//             // Total row style
//             $styles[$currentRow] = [
//                 'font' => ['bold' => true],
//                 'fill' => [
//                     'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
//                     'startColor' => ['rgb' => 'C8E6C9']
//                 ]
//             ];
//             $currentRow++;

//             $currentRow += 2;
//         }

//         return $styles;
//     }
// }