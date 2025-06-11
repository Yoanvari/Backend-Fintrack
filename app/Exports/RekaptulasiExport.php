<?php

namespace App\Exports;

use App\Models\Transaction;
use App\Exports\Transaction\PemasukanSheet;
use App\Exports\Transaction\PengeluaranSheet;
use App\Exports\Anggaran\RencanaAnggaranSheet;
use App\Exports\Anggaran\RealisasiVsAnggaranSheet;
use App\Exports\Rekapitulasi\RekapitulasiSheet;
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
            new RekapitulasiSheet($this->branchId),
            new PemasukanSheet($this->branchId),
            new PengeluaranSheet($this->branchId),
            new RencanaAnggaranSheet($this->branchId),
            new RealisasiVsAnggaranSheet($this->branchId),
        ];
    }
}