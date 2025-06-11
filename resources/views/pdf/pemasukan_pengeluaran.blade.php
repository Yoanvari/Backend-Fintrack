<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Keuangan</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h3 { margin-top: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background-color: #E2E8F0; }
        .total-row { background-color: #FEF3C7; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Laporan Rekapitulasi Keuangan</h1>
    
    <div class="spacer"></div>
    <hr>
    <div class="spacer"></div>

    {{-- Pemasukan --}}
    <h2>Pemasukan</h2>
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
                    <td>{{ number_format($txn->amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Page Break --}}
    <div style="page-break-after: always;"></div>

    {{-- Pengeluaran --}}
    <h2>Pengeluaran</h2>
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
                    <td>{{ number_format($txn->amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>