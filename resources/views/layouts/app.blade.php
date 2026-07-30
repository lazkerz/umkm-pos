<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }
    </style>
</head>
<body class="bg-stone-100 text-stone-800 antialiased">

@auth
<div class="min-h-screen flex">

    {{-- SIDEBAR --}}
    <aside class="no-print w-64 flex-shrink-0 bg-gradient-to-b from-amber-950 via-amber-900 to-amber-950 text-amber-50 flex flex-col fixed inset-y-0">
        <div class="p-5 border-b border-amber-800/60">
            <div class="flex items-center gap-2">
                <div class="w-9 h-9 rounded-xl bg-amber-500 flex items-center justify-center text-lg shadow-lg shadow-amber-900/40">🏪</div>
                <div>
                    <h1 class="font-extrabold text-base leading-none tracking-tight">{{ config('app.name') }}</h1>
                    <p class="text-[11px] text-amber-300/80 leading-none mt-1">Management System</p>
                </div>
            </div>

            <div class="mt-4 flex items-center gap-2 bg-amber-900/50 rounded-lg px-3 py-2">
                <div class="w-7 h-7 rounded-full bg-amber-600 flex items-center justify-center text-xs font-bold uppercase">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-semibold truncate">{{ auth()->user()->name }}</p>
                    <span class="text-[10px] uppercase tracking-wide text-amber-300/80">{{ auth()->user()->role }}</span>
                </div>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto p-3 space-y-0.5 text-sm">
            @php
                $navItem = function ($route, $label, $icon, $params = []) {
                    $active = request()->routeIs($route) || request()->routeIs($route.'.*');
                    $classes = $active
                        ? 'flex items-center gap-2.5 px-3 py-2 rounded-lg bg-amber-500 text-amber-950 font-semibold shadow-sm'
                        : 'flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-amber-800/60 text-amber-100/90';
                    return '<a href="'.route($route, $params).'" class="'.$classes.'"><span class="text-base leading-none">'.$icon.'</span><span>'.$label.'</span></a>';
                };
            @endphp

            @if(auth()->user()->isOwner())
                <p class="px-3 pt-1 pb-1.5 text-[10px] uppercase tracking-wider text-amber-400/70 font-semibold">Owner</p>
                {!! $navItem('owner.dashboard', 'Dashboard Semua Toko', '📊') !!}
                {!! $navItem('owner.stores.index', 'Kelola Toko', '🏪') !!}

                @if(isset($store))
                    <p class="px-3 pt-4 pb-1.5 text-[10px] uppercase tracking-wider text-amber-400/70 font-semibold">{{ $store->name }}</p>
                    {!! $navItem('owner.stores.staff.index', 'Staff / Kasir', '👥', $store) !!}
                    {!! $navItem('owner.stores.stock-distributions.index', 'Distribusi Stok', '🚚', $store) !!}
                    {!! $navItem('owner.stores.expenses.approval', 'Approve Pengeluaran', '✅', $store) !!}
                @endif
            @endif

            @if(isset($store))
                <p class="px-3 pt-4 pb-1.5 text-[10px] uppercase tracking-wider text-amber-400/70 font-semibold">Menu Toko</p>
                {!! $navItem('stores.dashboard', 'Dashboard Toko', '📈', $store) !!}
                {!! $navItem('stores.transactions.create', 'Kasir', '🧾', $store) !!}
                {!! $navItem('stores.transactions.index', 'Riwayat Transaksi', '📋', $store) !!}
                {!! $navItem('stores.reports.index', 'Laporan', '🗂️', $store) !!}
                {!! $navItem('stores.products.index', 'Menu', '🛍️', $store) !!}
                {!! $navItem('stores.categories.index', 'Kategori', '🏷️', $store) !!}
                {!! $navItem('stores.stock-items.index', 'Management Stok', '📦', $store) !!}
                {!! $navItem('stores.units.index', 'Satuan', '📏', $store) !!}
                {!! $navItem('stores.expenses.index', 'Pengeluaran', '💸', $store) !!}
                {!! $navItem('stores.promotions.index', 'Promo', '🎁', $store) !!}
                {!! $navItem('stores.customers.index', 'Customer', '🙋', $store) !!}
            @endif
        </nav>

        <div class="p-3 border-t border-amber-800/60">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-amber-800/60 text-amber-100/90 text-sm">
                    <span>🚪</span><span>Logout</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- MAIN --}}
    <div class="flex-1 ml-64 min-h-screen flex flex-col">
        {{-- TOP BAR --}}
        <header class="no-print bg-white border-b border-stone-200 px-6 py-3 flex items-center justify-between sticky top-0 z-10">
            <div class="text-sm text-stone-500">
                @if(isset($store))
                    <span class="font-medium text-stone-700">{{ $store->name }}</span>
                @else
                    <span class="font-medium text-stone-700">Semua Toko</span>
                @endif
            </div>
            <div class="text-xs text-stone-400 flex items-center gap-4">
                <span>{{ now()->translatedFormat('l, d F Y') }}</span>
                <a href="{{ route('profile.edit') }}" class="text-stone-500 hover:text-amber-800 font-medium">👤 Profil Saya</a>
            </div>
        </header>

        <main class="flex-1 p-6">
            @if(session('success'))
                <div class="mb-4 flex items-center gap-2 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl text-sm">
                    <span>✅</span> {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
@else
    @yield('content')
@endauth

</body>
</html>
