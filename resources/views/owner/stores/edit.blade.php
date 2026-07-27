@extends('layouts.app')
@section('title', 'Edit Toko')
@section('content')

<h1 class="text-2xl font-bold text-amber-900 mb-6">Edit Toko: {{ $store->name }}</h1>

<div class="bg-white rounded-lg shadow p-6 max-w-lg">
    <form method="POST" action="{{ route('owner.stores.update', $store) }}" class="space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm font-medium mb-1">Nama Toko</label>
            <input type="text" name="name" value="{{ old('name', $store->name) }}" required class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Alamat</label>
            <textarea name="address" class="w-full border rounded px-3 py-2">{{ old('address', $store->address) }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">No. Telepon</label>
            <input type="text" name="phone" value="{{ old('phone', $store->phone) }}" class="w-full border rounded px-3 py-2">
        </div>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" {{ $store->is_active ? 'checked' : '' }}> Toko Aktif
        </label>
        <button type="submit" class="bg-amber-800 text-white px-5 py-2 rounded hover:bg-amber-900">Update</button>
    </form>
</div>

@endsection
