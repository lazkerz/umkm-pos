@extends('layouts.app')
@section('title', 'Dashboard - ' . $store->name)
@section('content')

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Dashboard <span class="text-slate-400 font-medium">·</span> {{ $store->name }}</h1>
    <form method="GET">
        <select name="period" onchange="this.form.submit()" class="border border-slate-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Hari Ini</option>
            <option value="this_week" {{ $period === 'this_week' ? 'selected' : '' }}>Minggu Ini</option>
            <option value="this_month" {{ $period === 'this_month' ? 'selected' : '' }}>Bulan Ini</option>
            <option value="this_year" {{ $period === 'this_year' ? 'selected' : '' }}>Tahun Ini</option>
        </select>
    </form>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <x-stat-card label="Total Customer">{{ $totalCustomers }}</x-stat-card>
    <x-stat-card label="Customer Baru">{{ $newCustomersThisPeriod }}</x-stat-card>
    <x-stat-card label="Total Penjualan (periode ini)" tone="positive">Rp {{ number_format($salesReport->sum('total_sales'), 0, ',', '.') }}</x-stat-card>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <x-panel title="Report Penjualan" icon="chart-pie">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-slate-400 border-b border-slate-100">
                    <th class="pb-2 font-medium">Tanggal</th>
                    <th class="pb-2 font-medium">Channel</th>
                    <th class="pb-2 font-medium text-right">Omzet</th>
                    <th class="pb-2 font-medium text-right">Transaksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salesReport as $row)
                    <tr class="border-b border-slate-50 last:border-0">
                        <td class="py-2 text-slate-700">{{ \Carbon\Carbon::parse($row->date)->format('d M') }}</td>
                        <td class="py-2 capitalize text-slate-700">{{ $row->channel }}</td>
                        <td class="py-2 text-right text-slate-700">Rp {{ number_format($row->total_sales, 0, ',', '.') }}</td>
                        <td class="py-2 text-right text-slate-700">{{ $row->total_transactions }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-4 text-center text-slate-400">Belum ada penjualan periode ini</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-panel>

    <x-panel title="Menu Terlaris" icon="bag">
        <table class="w-full text-sm">
            <tbody>
                @forelse($topProducts as $p)
                    <tr class="border-b border-slate-50 last:border-0">
                        <td class="py-2 text-slate-700">{{ $p->name }}</td>
                        <td class="py-2 text-right text-slate-700">{{ $p->total_sold }} terjual</td>
                    </tr>
                @empty
                    <tr><td class="py-4 text-center text-slate-400">Belum ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-panel>

    <x-panel title="Data Customer (Top Spender)" icon="contact" class="lg:col-span-2">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-slate-400 border-b border-slate-100">
                    <th class="pb-2 font-medium">Nama</th>
                    <th class="pb-2 font-medium">Kontak</th>
                    <th class="pb-2 font-medium text-right">Jumlah Transaksi</th>
                    <th class="pb-2 font-medium text-right">Total Belanja</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topCustomers as $c)
                    <tr class="border-b border-slate-50 last:border-0">
                        <td class="py-2 text-slate-700">{{ $c->name }}</td>
                        <td class="py-2 text-slate-500">{{ $c->phone ?? $c->email ?? '-' }}</td>
                        <td class="py-2 text-right text-slate-700">{{ $c->transactions_count }}</td>
                        <td class="py-2 text-right text-slate-700">Rp {{ number_format($c->transactions_sum_total ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-4 text-center text-slate-400">Belum ada customer</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-panel>
</div>

@endsection
