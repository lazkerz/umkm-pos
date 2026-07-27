@extends('layouts.app')
@section('title', 'Pengeluaran - ' . $store->name)
@section('content')

<h1 class="text-2xl font-bold text-amber-900 mb-6">Pengeluaran</h1>

<div class="grid grid-cols-3 gap-6">
    <div class="col-span-2 bg-white rounded-lg shadow p-5">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="pb-2">Tanggal</th>
                    <th class="pb-2">Kategori</th>
                    <th class="pb-2 text-right">Jumlah</th>
                    <th class="pb-2">Status</th>
                    <th class="pb-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $e)
                    <tr class="border-b last:border-0">
                        <td class="py-2">{{ $e->expense_date->format('d M Y') }}</td>
                        <td class="py-2">{{ $e->category }}</td>
                        <td class="py-2 text-right">Rp {{ number_format($e->amount, 0, ',', '.') }}</td>
                        <td class="py-2">
                            <span @class([
                                'text-xs px-2 py-0.5 rounded',
                                'bg-yellow-100 text-yellow-700' => $e->status === 'pending',
                                'bg-green-100 text-green-700' => $e->status === 'approved',
                                'bg-red-100 text-red-700' => $e->status === 'rejected',
                            ])>{{ $e->status }}</span>
                        </td>
                        <td class="py-2 text-right">
                            @if($e->status === 'pending')
                                <form method="POST" action="{{ route('stores.expenses.destroy', [$store, $e]) }}" onsubmit="return confirm('Hapus pengeluaran ini?')" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 text-xs hover:underline">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-4 text-center text-gray-400">Belum ada pengeluaran</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-3">{{ $expenses->links() }}</div>
    </div>

    <div class="bg-white rounded-lg shadow p-5">
        <h2 class="font-bold mb-3">Input Pengeluaran</h2>
        <form method="POST" action="{{ route('stores.expenses.store', $store) }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium mb-1">Kategori</label>
                <input type="text" name="category" placeholder="Sewa, Listrik, Gaji, dll" required class="w-full border rounded px-3 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Jumlah</label>
                <input type="number" step="0.01" name="amount" required class="w-full border rounded px-3 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Tanggal</label>
                <input type="date" name="expense_date" value="{{ now()->toDateString() }}" required class="w-full border rounded px-3 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Deskripsi</label>
                <textarea name="description" class="w-full border rounded px-3 py-1.5 text-sm"></textarea>
            </div>
            <button type="submit" class="w-full bg-amber-800 text-white py-2 rounded text-sm hover:bg-amber-900">
                Catat Pengeluaran
            </button>
            <p class="text-xs text-gray-400">*Kalau kamu Staff, pengeluaran ini nunggu approval Owner dulu.</p>
        </form>
    </div>
</div>

@endsection
