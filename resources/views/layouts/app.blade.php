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
<body class="bg-slate-50 text-slate-800 antialiased">

@auth
<div class="min-h-screen lg:flex" x-data="{ sidebarOpen: false }">

    {{-- MOBILE BACKDROP --}}
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
        class="no-print fixed inset-0 bg-slate-900/50 z-30 lg:hidden"
        x-transition:enter="transition-opacity ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    </div>

    {{-- SIDEBAR --}}
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="no-print w-64 flex-shrink-0 bg-slate-900 text-slate-300 flex flex-col fixed inset-y-0 z-40 transition-transform duration-200 lg:translate-x-0 lg:z-auto">
        <div class="p-5 border-b border-white/10">
            <div class="flex items-center justify-between gap-2.5">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-9 h-9 rounded-lg bg-indigo-500 flex items-center justify-center text-white shadow-lg shadow-indigo-950/40 flex-shrink-0">
                        <x-icon name="store" class="w-5 h-5" />
                    </div>
                    <div class="min-w-0">
                        <h1 class="font-bold text-base leading-none tracking-tight text-white truncate">{{ config('app.name') }}</h1>
                        <p class="text-[11px] text-slate-400 leading-none mt-1">Management System</p>
                    </div>
                </div>
                <button type="button" @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white flex-shrink-0" aria-label="Tutup menu">
                    <x-icon name="x" class="w-5 h-5" />
                </button>
            </div>

            <div class="mt-4 flex items-center gap-2 bg-white/5 rounded-lg px-3 py-2">
                <div class="w-7 h-7 rounded-full bg-indigo-500 text-white flex items-center justify-center text-xs font-bold uppercase">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-semibold truncate text-white">{{ auth()->user()->name }}</p>
                    <span class="text-[10px] uppercase tracking-wide text-slate-400">{{ auth()->user()->role }}</span>
                </div>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto p-3 space-y-0.5 text-sm">
            @php
                $navGroups = [];

                if (auth()->user()->isOwner()) {
                    $navGroups[] = [
                        'label' => 'Owner',
                        'items' => [
                            ['route' => 'owner.dashboard', 'label' => 'Dashboard Semua Toko', 'icon' => 'dashboard'],
                            ['route' => 'owner.stores.index', 'label' => 'Kelola Toko', 'icon' => 'store'],
                        ],
                    ];

                    if (isset($store)) {
                        $navGroups[] = [
                            'label' => $store->name,
                            'items' => [
                                ['route' => 'owner.stores.staff.index', 'label' => 'Staff / Kasir', 'icon' => 'users', 'params' => $store],
                                ['route' => 'owner.stores.stock-distributions.index', 'label' => 'Distribusi Stok', 'icon' => 'truck', 'params' => $store],
                                ['route' => 'owner.stores.expenses.approval', 'label' => 'Approve Pengeluaran', 'icon' => 'check-circle', 'params' => $store],
                            ],
                        ];
                    }
                }

                if (isset($store)) {
                    $navGroups[] = [
                        'label' => 'Menu Toko',
                        'items' => [
                            ['route' => 'stores.dashboard', 'label' => 'Dashboard Toko', 'icon' => 'dashboard', 'params' => $store],
                            ['route' => 'stores.transactions.create', 'label' => 'Kasir', 'icon' => 'cart', 'params' => $store],
                            ['route' => 'stores.transactions.index', 'label' => 'Riwayat Transaksi', 'icon' => 'list', 'params' => $store],
                            ['route' => 'stores.reports.index', 'label' => 'Laporan', 'icon' => 'report', 'params' => $store],
                            ['route' => 'stores.products.index', 'label' => 'Menu', 'icon' => 'bag', 'params' => $store],
                            ['route' => 'stores.categories.index', 'label' => 'Kategori', 'icon' => 'tag', 'params' => $store],
                            ['route' => 'stores.stock-items.index', 'label' => 'Management Stok', 'icon' => 'box', 'params' => $store],
                            ['route' => 'stores.units.index', 'label' => 'Satuan', 'icon' => 'scale', 'params' => $store],
                            ['route' => 'stores.expenses.index', 'label' => 'Pengeluaran', 'icon' => 'cash', 'params' => $store],
                            ['route' => 'stores.promotions.index', 'label' => 'Promo', 'icon' => 'gift', 'params' => $store],
                            ['route' => 'stores.customers.index', 'label' => 'Customer', 'icon' => 'contact', 'params' => $store],
                        ],
                    ];
                }
            @endphp

            @foreach($navGroups as $group)
                <p class="px-3 {{ $loop->first ? 'pt-1' : 'pt-4' }} pb-1.5 text-[10px] uppercase tracking-wider text-slate-500 font-semibold">{{ $group['label'] }}</p>
                @foreach($group['items'] as $item)
                    @php
                        $active = request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*');
                    @endphp
                    <a href="{{ route($item['route'], $item['params'] ?? []) }}"
                        @class([
                            'flex items-center gap-2.5 px-3 py-2 rounded-lg',
                            'bg-indigo-500 text-white font-semibold shadow-sm shadow-indigo-950/30' => $active,
                            'hover:bg-white/5 text-slate-300' => ! $active,
                        ])>
                        <x-icon :name="$item['icon']" class="w-4 h-4 flex-shrink-0" />
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            @endforeach
        </nav>

        <div class="p-3 border-t border-white/10">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-white/5 text-slate-300 text-sm">
                    <x-icon name="logout" class="w-4 h-4" />
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- MAIN --}}
    <div class="flex-1 lg:ml-64 min-h-screen flex flex-col min-w-0">
        {{-- TOP BAR --}}
        <header class="no-print bg-white border-b border-slate-200 px-4 sm:px-6 py-3 flex items-center justify-between sticky top-0 z-20">
            <div class="flex items-center gap-3 min-w-0">
                <button type="button" @click="sidebarOpen = true" class="lg:hidden text-slate-500 hover:text-slate-800 flex-shrink-0" aria-label="Buka menu">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" class="w-6 h-6">
                        <path d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div class="text-sm text-slate-500 truncate">
                    @if(isset($store))
                        <span class="font-medium text-slate-700">{{ $store->name }}</span>
                    @else
                        <span class="font-medium text-slate-700">Semua Toko</span>
                    @endif
                </div>
            </div>
            <div class="text-xs text-slate-400 flex items-center gap-4 flex-shrink-0">
                <span class="hidden sm:inline">{{ now()->translatedFormat('l, d F Y') }}</span>
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-1.5 text-slate-500 hover:text-indigo-600 font-medium">
                    <x-icon name="user" class="w-4 h-4" /> <span class="hidden sm:inline">Profil Saya</span>
                </a>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-6">
            @if(session('success'))
                <div class="mb-4 flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm">
                    <x-icon name="check-circle" class="w-4 h-4 flex-shrink-0" /> {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 flex items-start gap-2 bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-sm">
                    <x-icon name="warning" class="w-4 h-4 flex-shrink-0 mt-0.5" />
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
