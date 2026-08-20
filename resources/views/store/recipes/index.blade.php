@extends('layouts.app')
@section('title', 'Resep - ' . $product->name)
@section('content')

<h1 class="text-2xl font-bold text-amber-900 mb-1">Resep Menu: {{ $product->name }}</h1>
<p class="text-sm text-gray-500 mb-6">Bahan baku apa aja yang dipakai untuk 1 unit menu ini terjual.</p>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white rounded-lg shadow p-5">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="pb-2">Bahan Baku</th>
                    <th class="pb-2 text-right">Jumlah per 1 Unit Terjual</th>
                    <th class="pb-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($recipes as $r)
                    <tr class="border-b last:border-0">
                        <td class="py-2">{{ $r->stockItem->name }}</td>
                        <td class="py-2 text-right">{{ $r->quantity_needed }} {{ $r->stockItem->unit->symbol }}</td>
                        <td class="py-2 text-right">
                            <form method="POST" action="{{ route('stores.products.recipes.destroy', [$store, $product, $r]) }}" onsubmit="return confirm('Hapus dari resep?')" class="inline">
                                @csrf @method('DELETE')
                                <button class="text-red-600 text-xs hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-4 text-center text-gray-400">Belum ada resep. Menu ini bisa tetap dijual, tapi stok bahan baku ga otomatis kepotong.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-5">
        <h2 class="font-bold mb-3">Tambah Bahan ke Resep</h2>
        <form method="POST" action="{{ route('stores.products.recipes.store', [$store, $product]) }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium mb-1">Bahan Baku</label>
                <select name="stock_item_id" required class="w-full border rounded px-3 py-1.5 text-sm">
                    <option value="">-- Pilih --</option>
                    @foreach($availableStockItems as $item)
                        <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->unit->symbol }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Jumlah per 1 unit terjual</label>
                <input type="number" step="0.001" name="quantity_needed" required placeholder="Misal: 18 (gram)" class="w-full border rounded px-3 py-1.5 text-sm">
            </div>
            <button type="submit" class="w-full bg-amber-800 text-white py-2 rounded text-sm hover:bg-amber-900">
                Simpan Resep
            </button>
        </form>
    </div>
</div>

@endsection
