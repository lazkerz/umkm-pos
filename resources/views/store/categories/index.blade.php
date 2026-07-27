@extends('layouts.app')
@section('title', 'Kategori - ' . $store->name)
@section('content')

<h1 class="text-2xl font-bold text-amber-900 mb-6">Kategori Menu</h1>

<div class="grid grid-cols-3 gap-6">
    <div class="col-span-2 bg-white rounded-lg shadow p-5">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="pb-2">Nama Kategori</th>
                    <th class="pb-2">Jumlah Menu</th>
                    <th class="pb-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $cat)
                    <tr class="border-b last:border-0">
                        <td class="py-2">{{ $cat->name }}</td>
                        <td class="py-2">{{ $cat->products_count }} menu</td>
                        <td class="py-2 text-right">
                            <form method="POST" action="{{ route('stores.categories.destroy', [$store, $cat]) }}" onsubmit="return confirm('Hapus kategori ini?')" class="inline">
                                @csrf @method('DELETE')
                                <button class="text-red-600 text-xs hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-4 text-center text-gray-400">Belum ada kategori</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-lg shadow p-5">
        <h2 class="font-bold mb-3">Tambah Kategori</h2>
        <form method="POST" action="{{ route('stores.categories.store', $store) }}" class="space-y-3">
            @csrf
            <input type="text" name="name" placeholder="Misal: Kopi Susu, Non-Kopi" required class="w-full border rounded px-3 py-1.5 text-sm">
            <button type="submit" class="w-full bg-amber-800 text-white py-2 rounded text-sm hover:bg-amber-900">Tambah</button>
        </form>
    </div>
</div>

@endsection
