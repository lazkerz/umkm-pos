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
        tfoot td { font-weight: bold; background: #fafafa; }
    </style>
</head>
<body>
    <h1>Laporan Penjualan</h1>
    <p class="subtitle">{{ $store->name }} &middot; Periode: {{ $periodLabel }}</p>

    <table>
        <thead>
            <tr>
                <th>Invoice</th><th>Tanggal</th><th>Channel</th><th>Customer</th><th>Item</th>
                <th class="text-right">Subtotal</th><th class="text-right">Diskon</th><th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $t)
                <tr>
                    <td>{{ $t->invoice_number }}</td>
                    <td>{{ $t->created_at->format('d-m-Y H:i') }}</td>
                    <td>{{ ucfirst($t->channel) }}</td>
                    <td>{{ $t->customer->name ?? 'Walk-in' }}</td>
                    <td>{{ $t->items->map(fn($i) => "{$i->product->name} x{$i->quantity}")->implode(', ') }}</td>
                    <td class="text-right">Rp {{ number_format($t->subtotal, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($t->discount, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($t->total, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="8">Tidak ada transaksi pada periode ini</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7">Total Omzet</td>
                <td class="text-right">Rp {{ number_format($transactions->sum('total'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <p style="margin-top: 30px; font-size: 10px; color: #999;">
        Dicetak pada {{ now()->format('d-m-Y H:i') }}
    </p>
</body>
</html>
