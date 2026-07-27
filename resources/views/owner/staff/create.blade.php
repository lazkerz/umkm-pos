@extends('layouts.app')
@section('title', 'Tambah Staff')
@section('content')

<h1 class="text-2xl font-bold text-amber-900 mb-6">Tambah Staff - {{ $store->name }}</h1>

<div class="bg-white rounded-lg shadow p-6 max-w-md">
    <form method="POST" action="{{ route('owner.stores.staff.store', $store) }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1">Nama</label>
            <input type="text" name="name" required class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input type="email" name="email" required class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Password</label>
            <input type="password" name="password" required class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">No. HP</label>
            <input type="text" name="phone" class="w-full border rounded px-3 py-2">
        </div>
        <button type="submit" class="bg-amber-800 text-white px-5 py-2 rounded hover:bg-amber-900">Tambah Staff</button>
    </form>
</div>

@endsection
