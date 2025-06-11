<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rencana Anggaran</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 6px; }
        th { background-color: #E2E8F0; }
        .header { background-color: #E3F2FD; font-weight: bold; padding: 5px; }
        .total-row { background-color: #C8E6C9; font-weight: bold; }
        .spacer { margin: 20px 0; }
    </style>
</head>
<body>
    <h2>Rencana Anggaran</h2>
@foreach ($budgets as $budget)
    <div class="header">Periode: {{ \Carbon\Carbon::parse($budget->period)->format('F Y') }}</div>
    <div class="header">Diajukan oleh: {{ $budget->user->name }}</div>
    <div class="header">Waktu Pengajuan: {{ \Carbon\Carbon::parse($budget->submission_date)->format('d/m/Y H:i') }}</div>
    <br>
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
                    <td style="text-align: right">{{ number_format($detail->amount, 0, ',', '.') }}</td>
                </tr>
                @php
                    $total += $detail->amount;
                @endphp
            @endforeach
            <tr class="total-row">
                <td colspan="3" style="text-align: right">TOTAL</td>
                <td style="text-align: right">{{ number_format($total, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
    <div class="spacer"></div>
    <hr>
    <div class="spacer"></div>
@endforeach
</body>
</html>
