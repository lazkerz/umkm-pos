@extends('layouts.app')
@section('title', 'Management Stok - ' . $store->name)
@section('content')

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-amber-900">Management Stok (Bahan Baku)</h1>
    <a href="{{ route('stores.units.index', $store) }}" class="text-sm text-amber-800 hover:underline">⚙️ Kelola Satuan</a>
</div>

<div class="grid grid-cols-3 gap-6">
    <div class="col-span-2 bg-white rounded-lg shadow p-5">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="pb-2">Nama Bahan</th>
                    <th class="pb-2 text-right">Stok Saat Ini</th>
                    <th class="pb-2 text-right">Min. Stok</th>
                    <th class="pb-2">Aksi Cepat</th>
                    <th class="pb-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($stockItems as $item)
                    <tr class="border-b last:border-0 {{ $item->isLowStock() ? 'bg-red-50' : '' }}">
                        <td class="py-2">{{ $item->name }}</td>
                        <td class="py-2 text-right font-medium {{ $item->isLowStock() ? 'text-red-600' : '' }}">
                            {{ $item->quantity }} {{ $item->unit->symbol }}
                        </td>
                        <td class="py-2 text-right text-gray-500">{{ $item->minimum_stock }} {{ $item->unit->symbol }}</td>
                        <td class="py-2">
                            <form method="POST" action="{{ route('stores.stock-items.adjust', [$store, $item]) }}" class="flex gap-1 items-center">
                                @csrf
                                <select name="type" class="border rounded px-1 py-1 text-xs">
                                    <option value="in">+ Masuk</option>
                                    <option value="out">- Keluar</option>
                                    <option value="adjustment">± Koreksi</option>
                                </select>
                                <input type="number" step="0.01" name="quantity" required placeholder="Jml" class="border rounded px-2 py-1 text-xs w-20">
                                <button type="submit" class="bg-gray-700 text-white text-xs px-2 py-1 rounded">OK</button>
                            </form>
                        </td>
                        <td class="py-2 text-right">
                            <form method="POST" action="{{ route('stores.stock-items.destroy', [$store, $item]) }}" onsubmit="return confirm('Hapus item stok ini?')" class="inline">
                                @csrf @method('DELETE')
                                <button class="text-red-600 text-xs hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-4 text-center text-gray-400">Belum ada item stok</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-lg shadow p-5">
        <h2 class="font-bold mb-3">Tambah Bahan Baku</h2>
        <form method="POST" action="{{ route('stores.stock-items.store', $store) }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium mb-1">Nama Bahan</label>
                <input type="text" name="name" placeholder="Biji Kopi Arabica" required class="w-full border rounded px-3 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Satuan</label>
                <select name="unit_id" required class="w-full border rounded px-3 py-1.5 text-sm">
                    <option value="">-- Pilih Satuan --</option>
                    @foreach($availableUnits as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->symbol }}){{ $unit->isCustom() ? ' - custom' : '' }}</option>
                    @endforeach
                </select>
                <a href="{{ route('stores.units.index', $store) }}" class="text-xs text-amber-800 hover:underline">+ Bikin satuan baru</a>
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Stok Awal</label>
                <input type="number" step="0.01" name="quantity" value="0" required class="w-full border rounded px-3 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Minimum Stok (alert)</label>
                <input type="number" step="0.01" name="minimum_stock" value="0" required class="w-full border rounded px-3 py-1.5 text-sm">
            </div>
            <button type="submit" class="w-full bg-amber-800 text-white py-2 rounded text-sm hover:bg-amber-900">
                Tambah Bahan
            </button>
        </form>
    </div>
</div>

@endsection
