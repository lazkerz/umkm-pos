@extends('layouts.app')
@section('title', 'Dashboard - ' . $store->name)
@section('content')

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-amber-900">Dashboard: {{ $store->name }}</h1>
    </div>
    <form method="GET" class="flex gap-2">
        <select name="period" onchange="this.form.submit()" class="border rounded px-3 py-1.5 text-sm">
            <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Hari Ini</option>
            <option value="this_week" {{ $period === 'this_week' ? 'selected' : '' }}>Minggu Ini</option>
            <option value="this_month" {{ $period === 'this_month' ? 'selected' : '' }}>Bulan Ini</option>
            <option value="this_year" {{ $period === 'this_year' ? 'selected' : '' }}>Tahun Ini</option>
        </select>
    </form>
</div>

<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-5">
        <p class="text-xs text-gray-500 uppercase">Total Customer</p>
        <p class="text-2xl font-bold">{{ $totalCustomers }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-5">
        <p class="text-xs text-gray-500 uppercase">Customer Baru</p>
        <p class="text-2xl font-bold">{{ $newCustomersThisPeriod }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-5">
        <p class="text-xs text-gray-500 uppercase">Total Penjualan (periode ini)</p>
        <p class="text-2xl font-bold text-green-700">Rp {{ number_format($salesReport->sum('total_sales'), 0, ',', '.') }}</p>
    </div>
</div>

<div class="grid grid-cols-2 gap-6">
    {{-- REPORT PENJUALAN --}}
    <div class="bg-white rounded-lg shadow p-5">
        <h2 class="font-bold mb-3">📈 Report Penjualan</h2>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="pb-2">Tanggal</th>
                    <th class="pb-2">Channel</th>
                    <th class="pb-2 text-right">Omzet</th>
                    <th class="pb-2 text-right">Transaksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salesReport as $row)
                    <tr class="border-b last:border-0">
                        <td class="py-2">{{ \Carbon\Carbon::parse($row->date)->format('d M') }}</td>
                        <td class="py-2 capitalize">{{ $row->channel }}</td>
                        <td class="py-2 text-right">Rp {{ number_format($row->total_sales, 0, ',', '.') }}</td>
                        <td class="py-2 text-right">{{ $row->total_transactions }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-4 text-center text-gray-400">Belum ada penjualan periode ini</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- TOP PRODUCTS --}}
    <div class="bg-white rounded-lg shadow p-5">
        <h2 class="font-bold mb-3">🔥 Menu Terlaris</h2>
        <table class="w-full text-sm">
            <tbody>
                @forelse($topProducts as $p)
                    <tr class="border-b last:border-0">
                        <td class="py-2">{{ $p->name }}</td>
                        <td class="py-2 text-right">{{ $p->total_sold }} terjual</td>
                    </tr>
                @empty
                    <tr><td class="py-4 text-center text-gray-400">Belum ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- DATA CUSTOMER (TOP) --}}
    <div class="bg-white rounded-lg shadow p-5 col-span-2">
        <h2 class="font-bold mb-3">🙋 Data Customer (Top Spender)</h2>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="pb-2">Nama</th>
                    <th class="pb-2">Kontak</th>
                    <th class="pb-2 text-right">Jumlah Transaksi</th>
                    <th class="pb-2 text-right">Total Belanja</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topCustomers as $c)
                    <tr class="border-b last:border-0">
                        <td class="py-2">{{ $c->name }}</td>
                        <td class="py-2 text-gray-500">{{ $c->phone ?? $c->email ?? '-' }}</td>
                        <td class="py-2 text-right">{{ $c->transactions_count }}</td>
                        <td class="py-2 text-right">Rp {{ number_format($c->transactions_sum_total ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-4 text-center text-gray-400">Belum ada customer</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
