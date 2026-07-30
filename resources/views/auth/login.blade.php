@extends('layouts.app')
@section('title', 'Login - ' . config('app.name'))
@section('content')

<div class="min-h-screen flex">

    {{-- LEFT PANEL --}}
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-amber-950 via-amber-900 to-amber-800 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10"
             style="background-image: radial-gradient(circle at 20% 20%, white 1px, transparent 1px); background-size: 30px 30px;"></div>

        <div class="relative z-10 flex flex-col justify-between p-12 text-amber-50 w-full">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-amber-500 flex items-center justify-center text-2xl shadow-lg shadow-amber-950/50">🏪</div>
                <span class="font-extrabold text-xl tracking-tight">{{ config('app.name') }}</span>
            </div>

            <div>
                <h1 class="text-4xl font-extrabold leading-tight mb-4">
                    Kelola bisnis kamu,<br>dari satu tempat.
                </h1>
                <p class="text-amber-200/80 text-lg max-w-md">
                    Kasir, stok bahan baku, laporan laba rugi, sampai promo — semua toko, satu dashboard.
                </p>
            </div>

            <p class="text-xs text-amber-300/60">&copy; {{ date('Y') }} {{ config('app.name') }} Management System</p>
        </div>
    </div>

    {{-- RIGHT PANEL: FORM --}}
    <div class="flex-1 flex items-center justify-center bg-stone-50 p-8">
        <div class="w-full max-w-sm">
            <div class="lg:hidden flex items-center gap-2 mb-8 justify-center">
                <div class="w-9 h-9 rounded-xl bg-amber-800 flex items-center justify-center text-lg">🏪</div>
                <span class="font-extrabold text-lg text-stone-800">{{ config('app.name') }}</span>
            </div>

            <h2 class="text-2xl font-bold text-stone-800">Selamat datang kembali</h2>
            <p class="text-sm text-stone-500 mt-1 mb-8">Login untuk masuk ke dashboard kamu</p>

            @if(session('status'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-2.5 rounded-lg text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-2.5 rounded-lg text-sm">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        placeholder="nama@email.com"
                        class="w-full border border-stone-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label class="block text-sm font-medium text-stone-700">Password</label>
                        @if(\Illuminate\Support\Facades\Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs text-amber-800 hover:underline">Lupa password?</a>
                        @endif
                    </div>
                    <input type="password" name="password" required
                        placeholder="••••••••"
                        class="w-full border border-stone-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                </div>

                <label class="flex items-center gap-2 text-sm text-stone-600">
                    <input type="checkbox" name="remember" class="rounded border-stone-300 text-amber-600 focus:ring-amber-500">
                    Ingat saya
                </label>

                <button type="submit"
                    class="w-full bg-amber-800 text-white py-2.5 rounded-lg font-medium hover:bg-amber-900 transition shadow-sm shadow-amber-900/20">
                    Masuk
                </button>
            </form>

            @if(\Illuminate\Support\Facades\Route::has('register'))
                <p class="text-sm text-stone-500 mt-6 text-center">
                    Belum punya akun? <a href="{{ route('register') }}" class="text-amber-800 font-semibold hover:underline">Daftar sebagai Owner</a>
                </p>
            @endif
        </div>
    </div>
</div>

@endsection
