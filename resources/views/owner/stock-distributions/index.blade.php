@extends('layouts.app')
@section('title', 'Distribusi Stok - ' . $store->name)
@section('content')

<h1 class="text-2xl font-bold text-amber-900 mb-1">Distribusi Stok</h1>
<p class="text-sm text-gray-500 mb-6">Kirim bahan baku ke toko: {{ $store->name }}</p>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white rounded-lg shadow p-5">
        <h2 class="font-bold mb-3">Histori Distribusi</h2>
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="pb-2">Tanggal</th>
                    <th class="pb-2">Bahan</th>
                    <th class="pb-2 text-right">Jumlah</th>
                    <th class="pb-2">Oleh</th>
                </tr>
            </thead>
            <tbody>
                @forelse($distributions as $d)
                    <tr class="border-b last:border-0">
                        <td class="py-2">{{ $d->distribution_date->format('d M Y') }}</td>
                        <td class="py-2">{{ $d->stockItem->name }}</td>
                        <td class="py-2 text-right">{{ $d->quantity }} {{ $d->stockItem->unit->symbol }}</td>
                        <td class="py-2 text-gray-500">{{ $d->distributor->name }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-4 text-center text-gray-400">Belum ada distribusi</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="mt-3">{{ $distributions->links() }}</div>
    </div>

    <div class="bg-white rounded-lg shadow p-5">
        <h2 class="font-bold mb-3">Kirim Stok Baru</h2>
        <form method="POST" action="{{ route('owner.stores.stock-distributions.store', $store) }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium mb-1">Bahan Baku</label>
                <select name="stock_item_id" required class="w-full border rounded px-3 py-1.5 text-sm">
                    <option value="">-- Pilih --</option>
                    @foreach($stockItems as $item)
                        <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->unit->symbol }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Jumlah</label>
                <input type="number" step="0.01" name="quantity" required class="w-full border rounded px-3 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Tanggal</label>
                <input type="date" name="distribution_date" value="{{ now()->toDateString() }}" required class="w-full border rounded px-3 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Catatan</label>
                <input type="text" name="note" class="w-full border rounded px-3 py-1.5 text-sm">
            </div>
            <button type="submit" class="w-full bg-amber-800 text-white py-2 rounded text-sm hover:bg-amber-900">
                Kirim Stok
            </button>
        </form>
    </div>
</div>

@endsection
