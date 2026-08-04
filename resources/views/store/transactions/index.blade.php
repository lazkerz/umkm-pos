@extends('layouts.app')
@section('title', 'Transaksi - ' . $store->name)
@section('content')

<div class="flex flex-wrap gap-3 justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-amber-900">Riwayat Transaksi</h1>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('stores.transactions.create', $store) }}?channel=offline" class="bg-amber-800 text-white px-4 py-2 rounded text-sm hover:bg-amber-900">+ Transaksi Offline</a>
        <a href="{{ route('stores.transactions.create', $store) }}?channel=online" class="bg-amber-600 text-white px-4 py-2 rounded text-sm hover:bg-amber-700">+ Order Online</a>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-5">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-gray-500 border-b">
                <th class="pb-2">Invoice</th>
                <th class="pb-2">Channel</th>
                <th class="pb-2">Customer</th>
                <th class="pb-2 text-right">Total</th>
                <th class="pb-2">Status</th>
                <th class="pb-2"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $t)
                <tr class="border-b last:border-0">
                    <td class="py-2 font-mono text-xs">{{ $t->invoice_number }}</td>
                    <td class="py-2 capitalize">{{ $t->channel }}</td>
                    <td class="py-2">{{ $t->customer->name ?? 'Walk-in' }}</td>
                    <td class="py-2 text-right">Rp {{ number_format($t->total, 0, ',', '.') }}</td>
                    <td class="py-2">
                        <span @class([
                            'text-xs px-2 py-0.5 rounded',
                            'bg-green-100 text-green-700' => $t->status === 'completed',
                            'bg-yellow-100 text-yellow-700' => $t->status === 'pending',
                            'bg-red-100 text-red-700' => $t->status === 'cancelled',
                        ])>{{ $t->status }}</span>
                    </td>
                    <td class="py-2 text-right">
                        <a href="{{ route('stores.transactions.show', [$store, $t]) }}" class="text-amber-800 text-xs hover:underline">Detail</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-4 text-center text-gray-400">Belum ada transaksi</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    <div class="mt-3">{{ $transactions->links() }}</div>
</div>

@endsection
