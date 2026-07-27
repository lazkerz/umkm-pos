<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        h1 { font-size: 18px; margin-bottom: 0; }
        p.subtitle { color: #777; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f5f5f5; }
        .text-right { text-align: right; }
        .profit-positive { color: #15803d; }
        .profit-negative { color: #b91c1c; }
        tfoot td { font-weight: bold; background: #fafafa; }
    </style>
</head>
<body>
    <h1>Laporan Laba Rugi - Semua Toko</h1>
    <p class="subtitle">Periode: {{ $periodLabel }}</p>

    <table>
        <thead>
            <tr>
                <th>Toko</th><th class="text-right">Pendapatan</th><th class="text-right">Pengeluaran</th><th class="text-right">Laba/Rugi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    <td>{{ $row->store_name }}</td>
                    <td class="text-right">Rp {{ number_format($row->revenue, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($row->expense, 0, ',', '.') }}</td>
                    <td class="text-right {{ $row->profit >= 0 ? 'profit-positive' : 'profit-negative' }}">
                        Rp {{ number_format($row->profit, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                <td class="text-right">Rp {{ number_format($rows->sum('revenue'), 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($rows->sum('expense'), 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($rows->sum('profit'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <p style="margin-top: 30px; font-size: 10px; color: #999;">
        Dicetak pada {{ now()->format('d-m-Y H:i') }}
    </p>
</body>
</html>
