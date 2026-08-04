@extends('layouts.app')
@section('title', 'Dashboard Owner')
@section('content')

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Dashboard <span class="text-slate-400 font-medium">·</span> Semua Toko</h1>

    <div class="flex items-center gap-2">
        <a href="{{ route('owner.reports.laba-rugi.pdf') }}?period={{ $period }}" class="flex items-center gap-1.5 bg-white border border-slate-200 text-slate-600 rounded-lg px-3 py-1.5 text-sm font-medium hover:bg-slate-50">
            <x-icon name="report" class="w-4 h-4" /> PDF
        </a>
        <a href="{{ route('owner.reports.laba-rugi.excel') }}?period={{ $period }}" class="flex items-center gap-1.5 bg-white border border-slate-200 text-slate-600 rounded-lg px-3 py-1.5 text-sm font-medium hover:bg-slate-50">
            <x-icon name="report" class="w-4 h-4" /> Excel
        </a>

        <form method="GET">
            <select name="period" onchange="this.form.submit()" class="border border-slate-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="this_month" {{ $period === 'this_month' ? 'selected' : '' }}>Bulan Ini</option>
                <option value="last_month" {{ $period === 'last_month' ? 'selected' : '' }}>Bulan Lalu</option>
                <option value="this_year" {{ $period === 'this_year' ? 'selected' : '' }}>Tahun Ini</option>
            </select>
        </form>
    </div>
</div>

{{-- LABA RUGI --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <x-stat-card label="Total Pendapatan" tone="positive">Rp {{ number_format($labaRugi['total_revenue'], 0, ',', '.') }}</x-stat-card>
    <x-stat-card label="Total Pengeluaran" tone="negative">Rp {{ number_format($labaRugi['total_expense'], 0, ',', '.') }}</x-stat-card>
    <x-stat-card label="Laba / Rugi" :tone="$labaRugi['total_profit'] >= 0 ? 'positive' : 'negative'">
        Rp {{ number_format($labaRugi['total_profit'], 0, ',', '.') }}
    </x-stat-card>
</div>

<div class="grid grid-cols-2 gap-6">
    <x-panel title="Store Performance" icon="chart-pie">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-slate-400 border-b border-slate-100">
                    <th class="pb-2 font-medium">Toko</th>
                    <th class="pb-2 font-medium text-right">Omzet</th>
                    <th class="pb-2 font-medium text-right">Transaksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($storePerformance as $row)
                    <tr class="border-b border-slate-50 last:border-0">
                        <td class="py-2 text-slate-700">{{ $row->store->name ?? '-' }}</td>
                        <td class="py-2 text-right text-slate-700">Rp {{ number_format($row->total_revenue, 0, ',', '.') }}</td>
                        <td class="py-2 text-right text-slate-700">{{ $row->total_transactions }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-4 text-center text-slate-400">Belum ada transaksi periode ini</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-panel>

    <x-panel title="Stok Menipis (Lintas Toko)" icon="warning">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-slate-400 border-b border-slate-100">
                    <th class="pb-2 font-medium">Bahan</th>
                    <th class="pb-2 font-medium">Toko</th>
                    <th class="pb-2 font-medium text-right">Sisa</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lowStockItems as $item)
                    <tr class="border-b border-slate-50 last:border-0">
                        <td class="py-2 text-slate-700">{{ $item->name }}</td>
                        <td class="py-2 text-slate-500">{{ $item->store->name }}</td>
                        <td class="py-2 text-right text-rose-600 font-medium">{{ $item->quantity }} {{ $item->unit->symbol }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-4 text-center text-slate-400">Semua stok aman</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-panel>

    <x-panel title="Customer per Toko" icon="contact">
        <table class="w-full text-sm">
            <tbody>
                @forelse($customerSummary as $row)
                    <tr class="border-b border-slate-50 last:border-0">
                        <td class="py-2 text-slate-700">Store #{{ $row->store_id }}</td>
                        <td class="py-2 text-right text-slate-700">{{ $row->total_customers }} customer</td>
                    </tr>
                @empty
                    <tr><td class="py-4 text-center text-slate-400">Belum ada data customer</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-panel>

    <x-panel title="Promo Aktif" icon="gift">
        <table class="w-full text-sm">
            <tbody>
                @forelse($promoSummary as $promo)
                    <tr class="border-b border-slate-50 last:border-0">
                        <td class="py-2 text-slate-700">{{ $promo->name }} <span class="text-slate-400">({{ $promo->store->name }})</span></td>
                        <td class="py-2 text-right text-slate-500">s/d {{ $promo->end_date->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td class="py-4 text-center text-slate-400">Tidak ada promo aktif</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-panel>
</div>

@endsection
