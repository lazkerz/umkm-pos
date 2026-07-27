@extends('layouts.app')
@section('title', 'Satuan Stok - ' . $store->name)
@section('content')

<h1 class="text-2xl font-bold text-amber-900 mb-1">Satuan Stok</h1>
<p class="text-sm text-gray-500 mb-6">Pilih dari satuan default, atau bikin satuan custom sendiri untuk toko ini.</p>

<div class="grid grid-cols-3 gap-6">
    <div class="col-span-2 space-y-6">
        {{-- DEFAULT UNITS --}}
        <div class="bg-white rounded-lg shadow p-5">
            <h2 class="font-bold mb-3">📏 Satuan Default (bisa dipakai semua toko)</h2>
            <div class="flex flex-wrap gap-2">
                @foreach($defaultUnits as $unit)
                    <span class="text-sm bg-gray-100 px-3 py-1 rounded-full">
                        {{ $unit->name }} <span class="text-gray-400">({{ $unit->symbol }})</span>
                    </span>
                @endforeach
            </div>
        </div>

        {{-- CUSTOM UNITS --}}
        <div class="bg-white rounded-lg shadow p-5">
            <h2 class="font-bold mb-3">✨ Satuan Custom Toko Ini</h2>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="pb-2">Nama</th>
                        <th class="pb-2">Simbol</th>
                        <th class="pb-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customUnits as $unit)
                        <tr class="border-b last:border-0">
                            <td class="py-2">{{ $unit->name }}</td>
                            <td class="py-2 text-gray-500">{{ $unit->symbol }}</td>
                            <td class="py-2 text-right">
                                <form method="POST" action="{{ route('stores.units.destroy', [$store, $unit]) }}" onsubmit="return confirm('Hapus satuan ini?')" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 text-xs hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-4 text-center text-gray-400">Belum ada satuan custom</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-5">
        <h2 class="font-bold mb-3">Bikin Satuan Custom</h2>
        <p class="text-xs text-gray-500 mb-3">Misal: "Scoop", "Shot", "Cup 250ml" — yang ga ada di daftar default.</p>
        <form method="POST" action="{{ route('stores.units.store', $store) }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium mb-1">Nama Satuan</label>
                <input type="text" name="name" placeholder="Scoop" required class="w-full border rounded px-3 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Simbol</label>
                <input type="text" name="symbol" placeholder="scp" required class="w-full border rounded px-3 py-1.5 text-sm">
            </div>
            <button type="submit" class="w-full bg-amber-800 text-white py-2 rounded text-sm hover:bg-amber-900">
                Buat Satuan Custom
            </button>
        </form>
    </div>
</div>

@endsection
