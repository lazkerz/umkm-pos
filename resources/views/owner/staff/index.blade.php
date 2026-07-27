@extends('layouts.app')
@section('title', 'Staff - ' . $store->name)
@section('content')

<h1 class="text-2xl font-bold text-amber-900 mb-1">Staff / Kasir</h1>
<p class="text-sm text-gray-500 mb-6">Toko: {{ $store->name }}</p>

<div class="grid grid-cols-3 gap-6">
    <div class="col-span-2 bg-white rounded-lg shadow p-5">
        <h2 class="font-bold mb-3">Daftar Staff</h2>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="pb-2">Nama</th>
                    <th class="pb-2">Email</th>
                    <th class="pb-2">Status</th>
                    <th class="pb-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($staff as $s)
                    <tr class="border-b last:border-0">
                        <td class="py-2">{{ $s->name }}</td>
                        <td class="py-2 text-gray-500">{{ $s->email }}</td>
                        <td class="py-2">
                            <span class="text-xs px-2 py-0.5 rounded {{ $s->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                                {{ $s->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="py-2 text-right">
                            <form method="POST" action="{{ route('owner.stores.staff.destroy', [$store, $s]) }}" onsubmit="return confirm('Hapus staff ini?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 text-xs hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-4 text-center text-gray-400">Belum ada staff</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-lg shadow p-5">
        <h2 class="font-bold mb-3">Tambah Staff Baru</h2>
        <form method="POST" action="{{ route('owner.stores.staff.store', $store) }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium mb-1">Nama</label>
                <input type="text" name="name" required class="w-full border rounded px-3 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Email</label>
                <input type="email" name="email" required class="w-full border rounded px-3 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Password</label>
                <input type="password" name="password" required class="w-full border rounded px-3 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">No. HP</label>
                <input type="text" name="phone" class="w-full border rounded px-3 py-1.5 text-sm">
            </div>
            <button type="submit" class="w-full bg-amber-800 text-white py-2 rounded text-sm hover:bg-amber-900">
                Tambah Staff
            </button>
        </form>
    </div>
</div>

@endsection
