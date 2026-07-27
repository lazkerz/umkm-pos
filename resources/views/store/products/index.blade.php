@extends('layouts.app')
@section('title', 'Menu - ' . $store->name)
@section('content')

<h1 class="text-2xl font-bold text-amber-900 mb-6">Menu ({{ $store->name }})</h1>

<div class="grid grid-cols-3 gap-6">
    <div class="col-span-2 bg-white rounded-lg shadow p-5">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="pb-2">Nama</th>
                    <th class="pb-2">Kategori</th>
                    <th class="pb-2 text-right">Harga</th>
                    <th class="pb-2">Status</th>
                    <th class="pb-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $p)
                    <tr class="border-b last:border-0">
                        <td class="py-2">{{ $p->name }}</td>
                        <td class="py-2 text-gray-500">{{ $p->category->name ?? '-' }}</td>
                        <td class="py-2 text-right">Rp {{ number_format($p->price, 0, ',', '.') }}</td>
                        <td class="py-2">
                            <span class="text-xs px-2 py-0.5 rounded {{ $p->is_available ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                                {{ $p->is_available ? 'Tersedia' : 'Habis' }}
                            </span>
                        </td>
                        <td class="py-2 text-right space-x-2">
                            <a href="{{ route('stores.products.recipes.index', [$store, $p]) }}" class="text-amber-800 text-xs hover:underline">Resep</a>
                            <form method="POST" action="{{ route('stores.products.destroy', [$store, $p]) }}" onsubmit="return confirm('Hapus menu ini?')" class="inline">
                                @csrf @method('DELETE')
                                <button class="text-red-600 text-xs hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-4 text-center text-gray-400">Belum ada menu</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-lg shadow p-5">
        <h2 class="font-bold mb-3">Tambah Menu Baru</h2>
        <form method="POST" action="{{ route('stores.products.store', $store) }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium mb-1">Nama Menu</label>
                <input type="text" name="name" required class="w-full border rounded px-3 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Kategori</label>
                <select name="category_id" class="w-full border rounded px-3 py-1.5 text-sm">
                    <option value="">-- Tanpa kategori --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Harga</label>
                <input type="number" name="price" step="0.01" required class="w-full border rounded px-3 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Deskripsi</label>
                <textarea name="description" class="w-full border rounded px-3 py-1.5 text-sm"></textarea>
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Foto (opsional)</label>
                <input type="file" name="image" class="w-full text-sm">
            </div>
            <button type="submit" class="w-full bg-amber-800 text-white py-2 rounded text-sm hover:bg-amber-900">
                Tambah Menu
            </button>
        </form>
    </div>
</div>

@endsection
