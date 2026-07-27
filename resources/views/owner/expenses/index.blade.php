@extends('layouts.app')
@section('title', 'Approve Pengeluaran - ' . $store->name)
@section('content')

<h1 class="text-2xl font-bold text-amber-900 mb-1">Approve Pengeluaran</h1>
<p class="text-sm text-gray-500 mb-6">Toko: {{ $store->name }}</p>

<div class="bg-white rounded-lg shadow p-5 mb-6">
    <h2 class="font-bold mb-3">🕐 Menunggu Persetujuan ({{ $pendingExpenses->count() }})</h2>
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-gray-500 border-b">
                <th class="pb-2">Tanggal</th>
                <th class="pb-2">Kategori</th>
                <th class="pb-2">Deskripsi</th>
                <th class="pb-2 text-right">Jumlah</th>
                <th class="pb-2">Diinput oleh</th>
                <th class="pb-2"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($pendingExpenses as $expense)
                <tr class="border-b last:border-0">
                    <td class="py-2">{{ $expense->expense_date->format('d M Y') }}</td>
                    <td class="py-2">{{ $expense->category }}</td>
                    <td class="py-2 text-gray-500">{{ $expense->description ?? '-' }}</td>
                    <td class="py-2 text-right">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                    <td class="py-2 text-gray-500">{{ $expense->creator->name }}</td>
                    <td class="py-2 text-right space-x-2">
                        <form method="POST" action="{{ route('owner.stores.expenses.approve', [$store, $expense]) }}" class="inline">
                            @csrf
                            <button class="text-green-700 text-xs font-medium hover:underline">Approve</button>
                        </form>
                        <form method="POST" action="{{ route('owner.stores.expenses.reject', [$store, $expense]) }}" class="inline">
                            @csrf
                            <button class="text-red-600 text-xs font-medium hover:underline">Tolak</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-4 text-center text-gray-400">Tidak ada pengeluaran pending 🎉</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="bg-white rounded-lg shadow p-5">
    <h2 class="font-bold mb-3">Riwayat Approval</h2>
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-gray-500 border-b">
                <th class="pb-2">Tanggal</th>
                <th class="pb-2">Kategori</th>
                <th class="pb-2 text-right">Jumlah</th>
                <th class="pb-2">Status</th>
                <th class="pb-2">Disetujui oleh</th>
            </tr>
        </thead>
        <tbody>
            @forelse($history as $expense)
                <tr class="border-b last:border-0">
                    <td class="py-2">{{ $expense->expense_date->format('d M Y') }}</td>
                    <td class="py-2">{{ $expense->category }}</td>
                    <td class="py-2 text-right">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                    <td class="py-2">
                        <span class="text-xs px-2 py-0.5 rounded {{ $expense->status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $expense->status }}
                        </span>
                    </td>
                    <td class="py-2 text-gray-500">{{ $expense->approver->name ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-4 text-center text-gray-400">Belum ada riwayat</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-3">{{ $history->links() }}</div>
</div>

@endsection
