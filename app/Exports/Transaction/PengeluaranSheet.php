<?php

namespace App\Exports\Transaction;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PengeluaranSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    protected $branchId;
    protected $rowCount = 0;

    public function __construct($branchId)
    {
        $this->branchId = $branchId;
    }

    public function collection()
    {
        $transactions = Transaction::with(['category', 'branch'])
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.branch_id', $this->branchId)
            ->where('categories.category_type', 'pengeluaran')
            ->orderBy('transactions.transaction_date', 'asc')
            ->get();

        $this->rowCount = $transactions->count();
        
        // Add total row
        $total = $transactions->sum('amount');
        $transactions->push((object)[
            'id' => null,
            'transaction_date' => null,
            'category' => (object)['category_name' => 'TOTAL PENGELUARAN'],
            'description' => null,
            'amount' => $total,
            'is_total' => true
        ]);

        return $transactions;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Kategori',
            'Deskripsi',
            'Jumlah'
        ];
    }

    public function map($transaction): array
    {
        static $counter = 0;
        
        if (isset($transaction->is_total) && $transaction->is_total) {
            return [
                '',
                '',
                $transaction->category->category_name,
                '',
                number_format($transaction->amount, 0, ',', '.')
            ];
        }

        $counter++;
        return [
            $counter,
            $transaction->transaction_date ? $transaction->transaction_date->format('d/m/Y') : '',
            $transaction->category->category_name ?? '',
            $transaction->description,
            number_format($transaction->amount, 0, ',', '.')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $totalRow = $this->rowCount + 2; // +1 for header, +1 for 0-index
        
        return [
            // Header style
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0']
                ]
            ],
            // Total row style
            $totalRow => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FEE2E2']
                ]
            ]
        ];
    }

    public function title(): string
    {
        return 'Pengeluaran';
    }
}
