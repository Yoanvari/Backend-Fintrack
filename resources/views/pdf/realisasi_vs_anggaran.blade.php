<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Realisasi vs Anggaran</title>
    <style>
        body { font-family: sans-serif; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #444;
            padding: 8px;
            text-align: left;
        }
        thead {
            background-color: #E2E8F0;
            font-weight: bold;
        }
        tfoot {
            background-color: #D1FAE5;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h2 style="text-align: center;">Laporan Realisasi vs Anggaran</h2>
    <table>
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
                        <td>{{ number_format($item->anggaran, 0, ',', '.') }}</td>
                        <td>{{ number_format($item->realisasi, 0, ',', '.') }}</td>
                        <td>{{ number_format($item->selisih, 0, ',', '.') }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
        <tfoot>
            @php
                $total = $data->firstWhere('is_total', true);
            @endphp
            <tr>
                <td>{{ $total->category_name }}</td>
                <td>{{ number_format($total->anggaran, 0, ',', '.') }}</td>
                <td>{{ number_format($total->realisasi, 0, ',', '.') }}</td>
                <td>{{ number_format($total->selisih, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
