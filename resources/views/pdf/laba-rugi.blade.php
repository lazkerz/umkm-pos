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
        .summary-table td:first-child { font-weight: bold; width: 200px; }
        .section-title { margin-top: 24px; font-size: 14px; font-weight: bold; }
        .profit-positive { color: #15803d; }
        .profit-negative { color: #b91c1c; }
    </style>
</head>
<body>
    <h1>Laporan Laba Rugi</h1>
    <p class="subtitle">{{ $store->name }} &middot; Periode: {{ $periodLabel }}</p>

    <table class="summary-table">
        <tr><td>Total Pendapatan</td><td>Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td></tr>
        <tr><td>Total Pengeluaran</td><td>Rp {{ number_format($totalExpense, 0, ',', '.') }}</td></tr>
        <tr>
            <td>Laba / Rugi</td>
            <td class="{{ $totalProfit >= 0 ? 'profit-positive' : 'profit-negative' }}">
                Rp {{ number_format($totalProfit, 0, ',', '.') }}
            </td>
        </tr>
    </table>

    <p class="section-title">Detail Pendapatan</p>
    <table>
        <thead>
            <tr>
                <th>Invoice</th><th>Tanggal</th><th>Channel</th><th>Customer</th><th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $t)
                <tr>
                    <td>{{ $t->invoice_number }}</td>
                    <td>{{ $t->created_at->format('d-m-Y H:i') }}</td>
                    <td>{{ ucfirst($t->channel) }}</td>
                    <td>{{ $t->customer->name ?? 'Walk-in' }}</td>
                    <td class="text-right">Rp {{ number_format($t->total, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Tidak ada transaksi pada periode ini</td></tr>
            @endforelse
        </tbody>
    </table>

    <p class="section-title">Detail Pengeluaran</p>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th><th>Kategori</th><th>Deskripsi</th><th class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $e)
                <tr>
                    <td>{{ $e->expense_date->format('d-m-Y') }}</td>
                    <td>{{ $e->category }}</td>
                    <td>{{ $e->description ?? '-' }}</td>
                    <td class="text-right">Rp {{ number_format($e->amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Tidak ada pengeluaran pada periode ini</td></tr>
            @endforelse
        </tbody>
    </table>

    <p style="margin-top: 30px; font-size: 10px; color: #999;">
        Dicetak pada {{ now()->format('d-m-Y H:i') }}
    </p>
</body>
</html>
