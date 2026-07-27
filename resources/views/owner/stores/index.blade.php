@extends('layouts.app')
@section('title', 'Kelola Toko')
@section('content')

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-amber-900">Toko Saya</h1>
    <a href="{{ route('owner.stores.create') }}" class="bg-amber-800 text-white px-4 py-2 rounded text-sm hover:bg-amber-900">
        + Buat Toko Baru
    </a>
</div>

<div class="grid grid-cols-3 gap-4">
    @forelse($stores as $store)
        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex justify-between items-start">
                <h3 class="font-bold text-lg">{{ $store->name }}</h3>
                <span class="text-xs px-2 py-0.5 rounded {{ $store->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                    {{ $store->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
            <p class="text-sm text-gray-500 mt-1">{{ $store->address ?? '-' }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $store->staff_count }} staff</p>

            <div class="flex gap-2 mt-4 text-sm">
                <a href="{{ route('stores.dashboard', $store) }}" class="text-amber-800 font-medium hover:underline">Buka Toko →</a>
                <a href="{{ route('owner.stores.edit', $store) }}" class="text-gray-500 hover:underline">Edit</a>
            </div>
        </div>
    @empty
        <p class="text-gray-400 col-span-3">Kamu belum punya toko. Yuk buat yang pertama!</p>
    @endforelse
</div>

@endsection
