@extends('layouts.app')
@section('title', 'Promo - ' . $store->name)
@section('content')

<h1 class="text-2xl font-bold text-amber-900 mb-6">Promo Management</h1>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white rounded-lg shadow p-5">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="pb-2">Nama</th>
                    <th class="pb-2">Tipe</th>
                    <th class="pb-2">Channel</th>
                    <th class="pb-2">Periode</th>
                    <th class="pb-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($promotions as $promo)
                    <tr class="border-b last:border-0">
                        <td class="py-2">{{ $promo->name }}</td>
                        <td class="py-2">{{ $promo->type === 'percentage' ? $promo->value . '%' : 'Rp ' . number_format($promo->value, 0, ',', '.') }}</td>
                        <td class="py-2 capitalize text-gray-500">{{ $promo->channel }}</td>
                        <td class="py-2 text-gray-500">{{ $promo->start_date->format('d/m') }} - {{ $promo->end_date->format('d/m/Y') }}</td>
                        <td class="py-2 text-right">
                            <form method="POST" action="{{ route('stores.promotions.destroy', [$store, $promo]) }}" onsubmit="return confirm('Hapus promo ini?')" class="inline">
                                @csrf @method('DELETE')
                                <button class="text-red-600 text-xs hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-4 text-center text-gray-400">Belum ada promo</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-5">
        <h2 class="font-bold mb-3">Buat Promo Baru</h2>
        <form method="POST" action="{{ route('stores.promotions.store', $store) }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium mb-1">Nama Promo</label>
                <input type="text" name="name" required class="w-full border rounded px-3 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Tipe Diskon</label>
                <select name="type" required class="w-full border rounded px-3 py-1.5 text-sm">
                    <option value="percentage">Persen (%)</option>
                    <option value="fixed">Potongan Nominal (Rp)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Nilai</label>
                <input type="number" step="0.01" name="value" required class="w-full border rounded px-3 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Berlaku di Channel</label>
                <select name="channel" required class="w-full border rounded px-3 py-1.5 text-sm">
                    <option value="both">Offline & Online</option>
                    <option value="offline">Offline saja</option>
                    <option value="online">Online saja</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Mulai</label>
                <input type="date" name="start_date" required class="w-full border rounded px-3 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Sampai</label>
                <input type="date" name="end_date" required class="w-full border rounded px-3 py-1.5 text-sm">
            </div>
            <button type="submit" class="w-full bg-amber-800 text-white py-2 rounded text-sm hover:bg-amber-900">
                Buat Promo
            </button>
        </form>
    </div>
</div>

@endsection
