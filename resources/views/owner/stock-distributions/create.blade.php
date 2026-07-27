@extends('layouts.app')
@section('title', 'Distribusi Stok Baru')
@section('content')

<h1 class="text-2xl font-bold text-amber-900 mb-6">Distribusi Stok Baru - {{ $store->name }}</h1>

<div class="bg-white rounded-lg shadow p-6 max-w-md">
    <form method="POST" action="{{ route('owner.stores.stock-distributions.store', $store) }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1">Bahan Baku</label>
            <select name="stock_item_id" required class="w-full border rounded px-3 py-2">
                <option value="">-- Pilih --</option>
                @foreach($stockItems as $item)
                    <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->unit->symbol }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Jumlah</label>
            <input type="number" step="0.01" name="quantity" required class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Tanggal</label>
            <input type="date" name="distribution_date" value="{{ now()->toDateString() }}" required class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Catatan</label>
            <input type="text" name="note" class="w-full border rounded px-3 py-2">
        </div>
        <button type="submit" class="bg-amber-800 text-white px-5 py-2 rounded hover:bg-amber-900">Kirim Stok</button>
    </form>
</div>

@endsection
