@extends('layouts.app')
@section('title', 'Profil Saya')
@section('content')

<h1 class="text-2xl font-bold text-stone-800 mb-6">Profil Saya</h1>

<div class="grid grid-cols-2 gap-6 max-w-3xl">
    {{-- INFO PROFIL --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-6">
        <h2 class="font-bold text-stone-800 mb-1">Informasi Profil</h2>
        <p class="text-sm text-stone-500 mb-4">Update nama, email, dan nomor HP kamu.</p>

        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf
            @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1.5">Nama</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                    class="w-full border border-stone-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1.5">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                    class="w-full border border-stone-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1.5">No. HP</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                    class="w-full border border-stone-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>
            <button type="submit" class="bg-amber-800 text-white px-5 py-2.5 rounded-lg font-medium hover:bg-amber-900 transition shadow-sm">
                Simpan Perubahan
            </button>
        </form>
    </div>

    {{-- GANTI PASSWORD --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-6">
        <h2 class="font-bold text-stone-800 mb-1">Ganti Password</h2>
        <p class="text-sm text-stone-500 mb-4">Pastikan pakai password yang kuat.</p>

        <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1.5">Password Saat Ini</label>
                <input type="password" name="current_password" required
                    class="w-full border border-stone-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1.5">Password Baru</label>
                <input type="password" name="password" required
                    class="w-full border border-stone-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1.5">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" required
                    class="w-full border border-stone-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>
            <button type="submit" class="bg-amber-800 text-white px-5 py-2.5 rounded-lg font-medium hover:bg-amber-900 transition shadow-sm">
                Ganti Password
            </button>
        </form>
    </div>
</div>

@endsection
