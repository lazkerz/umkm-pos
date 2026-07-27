@extends('layouts.app')
@section('title', 'Daftar - Kopi Senja')
@section('content')

<div class="min-h-screen flex">

    {{-- LEFT PANEL --}}
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-amber-950 via-amber-900 to-amber-800 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10"
             style="background-image: radial-gradient(circle at 20% 20%, white 1px, transparent 1px); background-size: 30px 30px;"></div>

        <div class="relative z-10 flex flex-col justify-between p-12 text-amber-50 w-full">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-amber-500 flex items-center justify-center text-2xl shadow-lg shadow-amber-950/50">☕</div>
                <span class="font-extrabold text-xl tracking-tight">Kopi Senja</span>
            </div>

            <div>
                <h1 class="text-4xl font-extrabold leading-tight mb-4">
                    Mulai kelola toko<br>kopi kamu hari ini.
                </h1>
                <p class="text-amber-200/80 text-lg max-w-md">
                    Daftar sebagai Owner, lalu buat toko pertama kamu dalam hitungan menit.
                </p>
            </div>

            <p class="text-xs text-amber-300/60">&copy; {{ date('Y') }} Kopi Senja Management System</p>
        </div>
    </div>

    {{-- RIGHT PANEL: FORM --}}
    <div class="flex-1 flex items-center justify-center bg-stone-50 p-8">
        <div class="w-full max-w-sm">
            <div class="lg:hidden flex items-center gap-2 mb-8 justify-center">
                <div class="w-9 h-9 rounded-xl bg-amber-800 flex items-center justify-center text-lg">☕</div>
                <span class="font-extrabold text-lg text-stone-800">Kopi Senja</span>
            </div>

            <h2 class="text-2xl font-bold text-stone-800">Daftar sebagai Owner</h2>
            <p class="text-sm text-stone-500 mt-1 mb-8">Bikin akun buat kelola toko kopi kamu</p>

            @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-2.5 rounded-lg text-sm">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1.5">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus
                        class="w-full border border-stone-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        placeholder="nama@email.com"
                        class="w-full border border-stone-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1.5">Password</label>
                    <input type="password" name="password" required
                        placeholder="Minimal 8 karakter"
                        class="w-full border border-stone-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1.5">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full border border-stone-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                </div>

                <button type="submit"
                    class="w-full bg-amber-800 text-white py-2.5 rounded-lg font-medium hover:bg-amber-900 transition shadow-sm shadow-amber-900/20 mt-2">
                    Daftar Sekarang
                </button>
            </form>

            <p class="text-sm text-stone-500 mt-6 text-center">
                Sudah punya akun? <a href="{{ route('login') }}" class="text-amber-800 font-semibold hover:underline">Login</a>
            </p>
        </div>
    </div>
</div>

@endsection
