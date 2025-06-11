<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan Lengkap</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        
        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #2563eb;
        }
        
        h2 {
            margin-top: 40px;
            margin-bottom: 20px;
            color: #1e40af;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        
        th {
            background-color: #E2E8F0;
            font-weight: bold;
        }
        
        .total-row {
            background-color: #FEF3C7;
            font-weight: bold;
        }
        
        .header {
            background-color: #E3F2FD;
            font-weight: bold;
            padding: 8px;
            margin: 10px 0;
            border-radius: 4px;
        }
        
        .spacer {
            margin: 20px 0;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        .summary-table th {
            background-color: #E2E8F0;
        }
        
        .budget-total-row {
            background-color: #C8E6C9;
            font-weight: bold;
        }
        
        .vs-table tfoot {
            background-color: #D1FAE5;
            font-weight: bold;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Laporan Keuangan Lengkap</h1>
    <div class="spacer"></div>
    <hr>
    <div class="spacer"></div>
    {{-- 1. Rekapitulasi Keuangan --}}
    <h2>1. Rekapitulasi Keuangan</h2>
    <table class="summary-table">
        <thead>
            <tr>
                <th>Keterangan</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Anggaran</td>
                <td>Rp {{ number_format($totalAnggaran, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Total Pemasukan</td>
                <td>Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Total Pengeluaran</td>
                <td>Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td><strong>Sisa Anggaran</strong></td>
                <td><strong>Rp {{ number_format($sisaAnggaran, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>
    
    {{-- 2. Rencana vs Realisasi --}}
    <h2>2. Laporan Realisasi vs Anggaran</h2>
    <table class="vs-table">
        <thead>
            <tr>
                <th>Kategori</th>
                <th>Anggaran</th>
                <th>Realisasi</th>
                <th>Selisih</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $item)
                @if (!isset($item->is_total))
                    <tr>
                        <td>{{ $item->category_name }}</td>
                        <td class="text-right">{{ number_format($item->anggaran, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($item->realisasi, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($item->selisih, 0, ',', '.') }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
        <tfoot>
            @php
                $total = $data->firstWhere('is_total', true);
            @endphp
            <tr>
                <td><strong>{{ $total->category_name }}</strong></td>
                <td class="text-right"><strong>{{ number_format($total->anggaran, 0, ',', '.') }}</strong></td>
                <td class="text-right"><strong>{{ number_format($total->realisasi, 0, ',', '.') }}</strong></td>
                <td class="text-right"><strong>{{ number_format($total->selisih, 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>
    
    <div class="page-break"></div>
    
    {{-- 3. Pemasukan --}}
    <h2>3. Detail Pemasukan</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Kategori</th>
                <th>Deskripsi</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach ($pemasukan as $txn)
                <tr class="{{ $txn->is_total ?? false ? 'total-row' : '' }}">
                    <td>{{ $txn->is_total ?? false ? '' : $no++ }}</td>
                    <td>{{ $txn->transaction_date ? \Carbon\Carbon::parse($txn->transaction_date)->format('d/m/Y') : '' }}</td>
                    <td>{{ $txn->category->category_name ?? '' }}</td>
                    <td>{{ $txn->description ?? '' }}</td>
                    <td class="text-right">{{ number_format($txn->amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="page-break"></div>
    
    {{-- 4. Pengeluaran --}}
    <h2>4. Detail Pengeluaran</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Kategori</th>
                <th>Deskripsi</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach ($pengeluaran as $txn)
                <tr class="{{ $txn->is_total ?? false ? 'total-row' : '' }}">
                    <td>{{ $txn->is_total ?? false ? '' : $no++ }}</td>
                    <td>{{ $txn->transaction_date ? \Carbon\Carbon::parse($txn->transaction_date)->format('d/m/Y') : '' }}</td>
                    <td>{{ $txn->category->category_name ?? '' }}</td>
                    <td>{{ $txn->description ?? '' }}</td>
                    <td class="text-right">{{ number_format($txn->amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="page-break"></div>
    
    {{-- 5. Rencana Anggaran --}}
    <h2>5. Rencana Anggaran</h2>
    @foreach ($budgets as $budget)
        <div class="header">Periode: {{ \Carbon\Carbon::parse($budget->period)->format('F Y') }}</div>
        <div class="header">Diajukan oleh: {{ $budget->user->name }}</div>
        <div class="header">Waktu Pengajuan: {{ \Carbon\Carbon::parse($budget->submission_date)->format('d/m/Y H:i') }}</div>
        
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kategori</th>
                    <th>Deskripsi</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $total = 0;
                @endphp
                @foreach ($budget->detail as $index => $detail)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $detail->category->category_name }}</td>
                        <td>{{ $detail->description }}</td>
                        <td class="text-right">{{ number_format($detail->amount, 0, ',', '.') }}</td>
                    </tr>
                    @php
                        $total += $detail->amount;
                    @endphp
                @endforeach
                <tr class="budget-total-row">
                    <td colspan="3" class="text-right">TOTAL</td>
                    <td class="text-right">{{ number_format($total, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
        
        @if (!$loop->last)
            <div class="spacer"></div>
            <hr>
            <div class="spacer"></div>
        @endif
    @endforeach
</body>
</html>