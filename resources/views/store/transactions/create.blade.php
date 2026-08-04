@extends('layouts.app')
@section('title', 'Kasir - ' . $store->name)
@section('content')

<div x-data="posCart({
        products: @js($productsForJs),
        categories: @js($categories),
        customers: @js($customers),
        promotions: @js($promotionsForJs),
        channel: @js($channel),
        checkoutUrl: @js(route('stores.transactions.store', $store)),
        customerStoreUrl: @js(route('stores.customers.store', $store)),
        csrfToken: @js(csrf_token()),
    })">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-slate-900">
            Kasir <span class="text-slate-400 font-medium">·</span> <span class="capitalize">{{ $channel }}</span>
        </h1>
        <div class="flex gap-1.5 text-sm bg-white border border-slate-200 rounded-lg p-1">
            <a href="?channel=offline" class="px-3 py-1.5 rounded-md {{ $channel === 'offline' ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">Offline</a>
            <a href="?channel=online" class="px-3 py-1.5 rounded-md {{ $channel === 'online' ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">Online</a>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        {{-- PILIHAN MENU --}}
        <div class="col-span-2">
            <div class="relative mb-4">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <x-icon name="search" class="w-4 h-4" />
                </span>
                <input type="text" x-model="search" placeholder="Cari produk..."
                    class="w-full border border-slate-200 rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div class="flex flex-wrap gap-2 mb-4 text-xs">
                <button type="button" @click="activeCategory = null"
                    :class="activeCategory === null ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'"
                    class="px-3 py-1.5 rounded-full font-medium">Semua</button>
                <template x-for="category in categories" :key="category.id">
                    <button type="button" @click="activeCategory = category.id"
                        :class="activeCategory === category.id ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'"
                        class="px-3 py-1.5 rounded-full font-medium" x-text="category.name"></button>
                </template>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <template x-for="product in filteredProducts" :key="product.id">
                    <button type="button" @click="addToCart(product.id)"
                        class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 text-left hover:border-indigo-400 hover:shadow-md transition">
                        <p class="font-medium text-sm text-slate-800" x-text="product.name"></p>
                        <p class="text-indigo-600 font-bold text-sm mt-1" x-text="formatMoney(product.price)"></p>
                    </button>
                </template>
                <p x-show="filteredProducts.length === 0" class="col-span-3 text-slate-400 text-sm text-center py-8">
                    Tidak ada produk yang cocok.
                </p>
            </div>
        </div>

        {{-- CART / INPUT --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 h-fit sticky top-6">
            <h2 class="flex items-center gap-2 font-semibold text-sm text-slate-800 mb-3">
                <x-icon name="cart" class="w-4 h-4 text-indigo-500" /> Pesanan
            </h2>

            <div class="space-y-2 mb-4 text-sm max-h-64 overflow-y-auto">
                <p x-show="isEmpty" class="text-slate-400 text-center py-4">Belum ada item dipilih</p>
                <template x-for="line in cartLines" :key="line.id">
                    <div class="flex justify-between items-center">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium truncate text-slate-800" x-text="line.product.name"></p>
                            <p class="text-xs text-slate-400" x-text="formatMoney(line.product.price) + ' x ' + line.qty"></p>
                        </div>
                        <div class="flex items-center gap-1">
                            <button type="button" @click="decrement(line.id)" class="w-6 h-6 flex items-center justify-center bg-slate-100 hover:bg-slate-200 rounded text-slate-600">
                                <x-icon name="minus" class="w-3 h-3" />
                            </button>
                            <span class="w-6 text-center" x-text="line.qty"></span>
                            <button type="button" @click="addToCart(line.id)" class="w-6 h-6 flex items-center justify-center bg-slate-100 hover:bg-slate-200 rounded text-slate-600">
                                <x-icon name="plus" class="w-3 h-3" />
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <div x-show="stockWarnings.length > 0" class="mb-3 flex gap-2 bg-amber-50 border border-amber-200 text-amber-800 px-3 py-2 rounded-lg text-xs">
                <x-icon name="warning" class="w-4 h-4 flex-shrink-0 mt-0.5" />
                <div class="space-y-1">
                    <p class="font-semibold">Stok bahan baku mungkin tidak cukup:</p>
                    <template x-for="warning in stockWarnings" :key="warning.stockItemId">
                        <p>Tersisa <span x-text="warning.available"></span>, dibutuhkan <span x-text="warning.qty"></span>.</p>
                    </template>
                </div>
            </div>

            <div x-show="errors.items" class="mb-3 bg-rose-50 border border-rose-200 text-rose-800 px-3 py-2 rounded-lg text-xs">
                <template x-for="message in (errors.items || [])" :key="message">
                    <p x-text="message"></p>
                </template>
            </div>

            <div class="mb-3">
                <label class="block text-xs font-medium text-slate-600 mb-1">Customer (opsional)</label>
                <div class="flex gap-1.5">
                    <select x-model="customerId" class="flex-1 border border-slate-200 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Walk-in</option>
                        <template x-for="customer in customers" :key="customer.id">
                            <option :value="customer.id" x-text="customer.name"></option>
                        </template>
                    </select>
                    <button type="button" @click="openQuickAdd()" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium">+ Baru</button>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-3 space-y-1 text-sm mb-3">
                <div class="flex justify-between text-slate-600"><span>Subtotal</span><span x-text="formatMoney(subtotal)"></span></div>
                <div class="flex justify-between items-center" x-show="promotions.length > 0">
                    <span class="text-slate-600">Promo</span>
                    <select x-model="promotionId" class="border border-slate-200 rounded-lg px-2 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Tanpa Promo</option>
                        <template x-for="promo in promotions" :key="promo.id">
                            <option :value="promo.id" x-text="promo.name + ' (' + (promo.type === 'percentage' ? promo.value + '%' : formatMoney(promo.value)) + ')'"></option>
                        </template>
                    </select>
                </div>
                <div class="flex justify-between font-bold text-base pt-1 text-slate-900"><span>Total</span><span x-text="formatMoney(total)"></span></div>
            </div>

            <div class="mb-3">
                <label class="block text-xs font-medium text-slate-600 mb-1">Metode Pembayaran</label>
                <select x-model="paymentMethod" class="w-full border border-slate-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="cash">Cash</option>
                    <option value="qris">QRIS</option>
                    <option value="transfer">Transfer</option>
                </select>
            </div>

            <button type="button" @click="submit()" :disabled="isEmpty || submitting"
                class="w-full bg-indigo-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 disabled:opacity-40 transition">
                <span x-show="!submitting">Proses Transaksi</span>
                <span x-show="submitting">Memproses...</span>
            </button>
        </div>
    </div>

    {{-- QUICK ADD CUSTOMER MODAL --}}
    <div x-show="quickAdd.open" x-cloak
        class="fixed inset-0 bg-slate-900/40 flex items-center justify-center z-50" @click.self="quickAdd.open = false">
        <div class="bg-white rounded-xl shadow-xl p-5 w-full max-w-sm">
            <h3 class="font-semibold text-slate-800 mb-3">Tambah Customer Baru</h3>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Nama</label>
                    <input type="text" x-model="quickAdd.name" class="w-full border border-slate-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Telepon (opsional)</label>
                    <input type="text" x-model="quickAdd.phone" class="w-full border border-slate-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Email (opsional)</label>
                    <input type="email" x-model="quickAdd.email" class="w-full border border-slate-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <p x-show="quickAdd.error" x-text="quickAdd.error" class="text-rose-600 text-xs"></p>

                <div class="flex gap-2 justify-end pt-2">
                    <button type="button" @click="quickAdd.open = false" class="px-3 py-1.5 rounded-lg text-sm bg-slate-100 hover:bg-slate-200 text-slate-700">Batal</button>
                    <button type="button" @click="submitQuickAdd()" :disabled="!quickAdd.name.trim() || quickAdd.submitting"
                        class="px-3 py-1.5 rounded-lg text-sm bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-40">Simpan</button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
