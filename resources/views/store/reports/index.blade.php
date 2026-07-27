@extends('layouts.app')
@section('title', 'Laporan - ' . $store->name)
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-stone-800">Laporan</h1>
        <p class="text-sm text-stone-500">{{ $store->name }}</p>
    </div>
    <form method="GET" class="flex gap-2">
        <select name="period" onchange="this.form.submit()" class="border border-stone-300 rounded-lg px-3 py-2 text-sm bg-white">
            <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Hari Ini</option>
            <option value="this_week" {{ $period === 'this_week' ? 'selected' : '' }}>Minggu Ini</option>
            <option value="this_month" {{ $period === 'this_month' ? 'selected' : '' }}>Bulan Ini</option>
            <option value="last_month" {{ $period === 'last_month' ? 'selected' : '' }}>Bulan Lalu</option>
            <option value="this_year" {{ $period === 'this_year' ? 'selected' : '' }}>Tahun Ini</option>
        </select>
    </form>
</div>

<div class="grid grid-cols-3 gap-4 mb-8">
    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-5">
        <p class="text-xs text-stone-400 uppercase font-medium">Total Pendapatan</p>
        <p class="text-2xl font-bold text-green-700 mt-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-5">
        <p class="text-xs text-stone-400 uppercase font-medium">Total Pengeluaran</p>
        <p class="text-2xl font-bold text-red-700 mt-1">Rp {{ number_format($totalExpense, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-5">
        <p class="text-xs text-stone-400 uppercase font-medium">Jumlah Transaksi</p>
        <p class="text-2xl font-bold text-stone-800 mt-1">{{ $totalTransactions }}</p>
    </div>
</div>

<div class="grid grid-cols-2 gap-6">
    {{-- LAPORAN LABA RUGI --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-6">
        <h2 class="font-bold text-stone-800 mb-1">📊 Laporan Laba Rugi</h2>
        <p class="text-sm text-stone-500 mb-4">Ringkasan pendapatan, pengeluaran, dan laba/rugi lengkap dengan rincian per transaksi & pengeluaran.</p>
        <div class="flex gap-2">
            <a href="{{ route('stores.reports.laba-rugi.pdf', $store) }}?period={{ $period }}"
               class="flex-1 text-center bg-red-50 text-red-700 border border-red-200 rounded-lg py-2 text-sm font-medium hover:bg-red-100">
                📄 Export PDF
            </a>
            <a href="{{ route('stores.reports.laba-rugi.excel', $store) }}?period={{ $period }}"
               class="flex-1 text-center bg-green-50 text-green-700 border border-green-200 rounded-lg py-2 text-sm font-medium hover:bg-green-100">
                📊 Export Excel
            </a>
        </div>
    </div>

    {{-- LAPORAN PENJUALAN --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-6">
        <h2 class="font-bold text-stone-800 mb-1">🧾 Laporan Penjualan</h2>
        <p class="text-sm text-stone-500 mb-4">Detail transaksi penjualan (offline & online) beserta item yang terjual.</p>
        <div class="flex gap-2">
            <a href="{{ route('stores.reports.sales.pdf', $store) }}?period={{ $period }}"
               class="flex-1 text-center bg-red-50 text-red-700 border border-red-200 rounded-lg py-2 text-sm font-medium hover:bg-red-100">
                📄 Export PDF
            </a>
            <a href="{{ route('stores.reports.sales.excel', $store) }}?period={{ $period }}"
               class="flex-1 text-center bg-green-50 text-green-700 border border-green-200 rounded-lg py-2 text-sm font-medium hover:bg-green-100">
                📊 Export Excel
            </a>
        </div>
    </div>
</div>

@endsection
