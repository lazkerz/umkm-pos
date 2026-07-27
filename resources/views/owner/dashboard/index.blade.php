@extends('layouts.app')
@section('title', 'Dashboard Owner')
@section('content')

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-amber-900">Dashboard (Semua Toko)</h1>

    <div class="flex items-center gap-2">
        <a href="{{ route('owner.reports.laba-rugi.pdf') }}?period={{ $period }}" class="bg-red-50 text-red-700 border border-red-200 rounded-lg px-3 py-1.5 text-sm font-medium hover:bg-red-100">📄 PDF</a>
        <a href="{{ route('owner.reports.laba-rugi.excel') }}?period={{ $period }}" class="bg-green-50 text-green-700 border border-green-200 rounded-lg px-3 py-1.5 text-sm font-medium hover:bg-green-100">📊 Excel</a>

        <form method="GET" class="flex gap-2">
            <select name="period" onchange="this.form.submit()" class="border rounded-lg px-3 py-1.5 text-sm">
                <option value="this_month" {{ $period === 'this_month' ? 'selected' : '' }}>Bulan Ini</option>
                <option value="last_month" {{ $period === 'last_month' ? 'selected' : '' }}>Bulan Lalu</option>
                <option value="this_year" {{ $period === 'this_year' ? 'selected' : '' }}>Tahun Ini</option>
            </select>
        </form>
    </div>
</div>

{{-- LABA RUGI --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-5">
        <p class="text-xs text-gray-500 uppercase">Total Pendapatan</p>
        <p class="text-2xl font-bold text-green-700">Rp {{ number_format($labaRugi['total_revenue'], 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-5">
        <p class="text-xs text-gray-500 uppercase">Total Pengeluaran</p>
        <p class="text-2xl font-bold text-red-700">Rp {{ number_format($labaRugi['total_expense'], 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-5">
        <p class="text-xs text-gray-500 uppercase">Laba / Rugi</p>
        <p class="text-2xl font-bold {{ $labaRugi['total_profit'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
            Rp {{ number_format($labaRugi['total_profit'], 0, ',', '.') }}
        </p>
    </div>
</div>

<div class="grid grid-cols-2 gap-6">
    {{-- STORE PERFORMANCE --}}
    <div class="bg-white rounded-lg shadow p-5">
        <h2 class="font-bold mb-3">🏆 Store Performance</h2>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="pb-2">Toko</th>
                    <th class="pb-2 text-right">Omzet</th>
                    <th class="pb-2 text-right">Transaksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($storePerformance as $row)
                    <tr class="border-b last:border-0">
                        <td class="py-2">{{ $row->store->name ?? '-' }}</td>
                        <td class="py-2 text-right">Rp {{ number_format($row->total_revenue, 0, ',', '.') }}</td>
                        <td class="py-2 text-right">{{ $row->total_transactions }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-4 text-center text-gray-400">Belum ada transaksi periode ini</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- MANAGEMENT STOK - ALERT --}}
    <div class="bg-white rounded-lg shadow p-5">
        <h2 class="font-bold mb-3">⚠️ Stok Menipis (Lintas Toko)</h2>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="pb-2">Bahan</th>
                    <th class="pb-2">Toko</th>
                    <th class="pb-2 text-right">Sisa</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lowStockItems as $item)
                    <tr class="border-b last:border-0">
                        <td class="py-2">{{ $item->name }}</td>
                        <td class="py-2 text-gray-500">{{ $item->store->name }}</td>
                        <td class="py-2 text-right text-red-600 font-medium">{{ $item->quantity }} {{ $item->unit->symbol }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-4 text-center text-gray-400">Semua stok aman ✅</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- CUSTOMER SUMMARY --}}
    <div class="bg-white rounded-lg shadow p-5">
        <h2 class="font-bold mb-3">🙋 Customer per Toko</h2>
        <table class="w-full text-sm">
            <tbody>
                @forelse($customerSummary as $row)
                    <tr class="border-b last:border-0">
                        <td class="py-2">Store #{{ $row->store_id }}</td>
                        <td class="py-2 text-right">{{ $row->total_customers }} customer</td>
                    </tr>
                @empty
                    <tr><td class="py-4 text-center text-gray-400">Belum ada data customer</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PROMO SUMMARY --}}
    <div class="bg-white rounded-lg shadow p-5">
        <h2 class="font-bold mb-3">🎁 Promo Aktif</h2>
        <table class="w-full text-sm">
            <tbody>
                @forelse($promoSummary as $promo)
                    <tr class="border-b last:border-0">
                        <td class="py-2">{{ $promo->name }} <span class="text-gray-400">({{ $promo->store->name }})</span></td>
                        <td class="py-2 text-right text-gray-500">s/d {{ $promo->end_date->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td class="py-4 text-center text-gray-400">Tidak ada promo aktif</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
