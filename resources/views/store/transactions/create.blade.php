@extends('layouts.app')
@section('title', 'Kasir - ' . $store->name)
@section('content')

<div x-data="posCart({
        products: @js($productsForJs),
        categories: @js($categories->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])),
        customers: @js($customers),
        promotions: @js($promotionsForJs),
        channel: @js($channel),
        checkoutUrl: @js(route('stores.transactions.store', $store)),
        customerStoreUrl: @js(route('stores.customers.store', $store)),
        csrfToken: @js(csrf_token()),
    })">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-amber-900">
            Kasir - <span class="capitalize">{{ $channel }}</span>
        </h1>
        <div class="flex gap-2 text-sm">
            <a href="?channel=offline" class="px-3 py-1.5 rounded {{ $channel === 'offline' ? 'bg-amber-800 text-white' : 'bg-gray-200' }}">Offline</a>
            <a href="?channel=online" class="px-3 py-1.5 rounded {{ $channel === 'online' ? 'bg-amber-800 text-white' : 'bg-gray-200' }}">Online</a>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        {{-- PILIHAN MENU --}}
        <div class="col-span-2">
            <div class="flex flex-col sm:flex-row gap-2 mb-4">
                <input type="text" x-model="search" placeholder="Cari produk..."
                    class="flex-1 border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>

            <div class="flex flex-wrap gap-2 mb-4 text-xs">
                <button type="button" @click="activeCategory = null"
                    :class="activeCategory === null ? 'bg-amber-800 text-white' : 'bg-gray-200'"
                    class="px-3 py-1.5 rounded-full">Semua</button>
                <template x-for="category in categories" :key="category.id">
                    <button type="button" @click="activeCategory = category.id"
                        :class="activeCategory === category.id ? 'bg-amber-800 text-white' : 'bg-gray-200'"
                        class="px-3 py-1.5 rounded-full" x-text="category.name"></button>
                </template>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <template x-for="product in filteredProducts" :key="product.id">
                    <button type="button" @click="addToCart(product.id)"
                        class="bg-white rounded-lg shadow p-4 text-left hover:ring-2 hover:ring-amber-500">
                        <p class="font-medium text-sm" x-text="product.name"></p>
                        <p class="text-amber-800 font-bold text-sm mt-1" x-text="formatMoney(product.price)"></p>
                    </button>
                </template>
                <p x-show="filteredProducts.length === 0" class="col-span-3 text-gray-400 text-sm text-center py-8">
                    Tidak ada produk yang cocok.
                </p>
            </div>
        </div>

        {{-- CART / INPUT --}}
        <div class="bg-white rounded-lg shadow p-5 h-fit sticky top-6">
            <h2 class="font-bold mb-3">🧾 Pesanan</h2>

            <div class="space-y-2 mb-4 text-sm max-h-64 overflow-y-auto">
                <p x-show="isEmpty" class="text-gray-400 text-center py-4">Belum ada item dipilih</p>
                <template x-for="line in cartLines" :key="line.id">
                    <div class="flex justify-between items-center">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium truncate" x-text="line.product.name"></p>
                            <p class="text-xs text-gray-400" x-text="formatMoney(line.product.price) + ' x ' + line.qty"></p>
                        </div>
                        <div class="flex items-center gap-1">
                            <button type="button" @click="decrement(line.id)" class="w-6 h-6 bg-gray-200 rounded">-</button>
                            <span class="w-6 text-center" x-text="line.qty"></span>
                            <button type="button" @click="addToCart(line.id)" class="w-6 h-6 bg-gray-200 rounded">+</button>
                        </div>
                    </div>
                </template>
            </div>

            <div x-show="stockWarnings.length > 0" class="mb-3 bg-yellow-50 border border-yellow-200 text-yellow-800 px-3 py-2 rounded text-xs space-y-1">
                <p class="font-semibold">⚠️ Stok bahan baku mungkin tidak cukup:</p>
                <template x-for="warning in stockWarnings" :key="warning.stockItemId">
                    <p>Tersisa <span x-text="warning.available"></span>, dibutuhkan <span x-text="warning.qty"></span>.</p>
                </template>
            </div>

            <div x-show="errors.items" class="mb-3 bg-red-50 border border-red-200 text-red-800 px-3 py-2 rounded text-xs">
                <template x-for="message in (errors.items || [])" :key="message">
                    <p x-text="message"></p>
                </template>
            </div>

            <div class="mb-3">
                <label class="block text-xs font-medium mb-1">Customer (opsional)</label>
                <div class="flex gap-1">
                    <select x-model="customerId" class="flex-1 border rounded px-2 py-1.5 text-xs">
                        <option value="">Walk-in</option>
                        <template x-for="customer in customers" :key="customer.id">
                            <option :value="customer.id" x-text="customer.name"></option>
                        </template>
                    </select>
                    <button type="button" @click="openQuickAdd()" class="px-2 py-1.5 bg-gray-200 rounded text-xs">+ Baru</button>
                </div>
            </div>

            <div class="border-t pt-3 space-y-1 text-sm mb-3">
                <div class="flex justify-between"><span>Subtotal</span><span x-text="formatMoney(subtotal)"></span></div>
                <div class="flex justify-between items-center" x-show="promotions.length > 0">
                    <span>Promo</span>
                    <select x-model="promotionId" class="border rounded px-2 py-1 text-xs">
                        <option value="">Tanpa Promo</option>
                        <template x-for="promo in promotions" :key="promo.id">
                            <option :value="promo.id" x-text="promo.name + ' (' + (promo.type === 'percentage' ? promo.value + '%' : formatMoney(promo.value)) + ')'"></option>
                        </template>
                    </select>
                </div>
                <div class="flex justify-between font-bold text-base pt-1"><span>Total</span><span x-text="formatMoney(total)"></span></div>
            </div>

            <div class="mb-3">
                <label class="block text-xs font-medium mb-1">Metode Pembayaran</label>
                <select x-model="paymentMethod" class="w-full border rounded px-3 py-1.5 text-sm">
                    <option value="cash">Cash</option>
                    <option value="qris">QRIS</option>
                    <option value="transfer">Transfer</option>
                </select>
            </div>

            <button type="button" @click="submit()" :disabled="isEmpty || submitting"
                class="w-full bg-amber-800 text-white py-2 rounded text-sm hover:bg-amber-900 disabled:opacity-40">
                <span x-show="!submitting">Proses Transaksi</span>
                <span x-show="submitting">Memproses...</span>
            </button>
        </div>
    </div>

    {{-- QUICK ADD CUSTOMER MODAL --}}
    <div x-show="quickAdd.open" x-cloak
        class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" @click.self="quickAdd.open = false">
        <div class="bg-white rounded-lg shadow-xl p-5 w-full max-w-sm">
            <h3 class="font-bold mb-3">Tambah Customer Baru</h3>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium mb-1">Nama</label>
                    <input type="text" x-model="quickAdd.name" class="w-full border rounded px-3 py-1.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1">Telepon (opsional)</label>
                    <input type="text" x-model="quickAdd.phone" class="w-full border rounded px-3 py-1.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1">Email (opsional)</label>
                    <input type="email" x-model="quickAdd.email" class="w-full border rounded px-3 py-1.5 text-sm">
                </div>

                <p x-show="quickAdd.error" x-text="quickAdd.error" class="text-red-600 text-xs"></p>

                <div class="flex gap-2 justify-end pt-2">
                    <button type="button" @click="quickAdd.open = false" class="px-3 py-1.5 rounded text-sm bg-gray-200">Batal</button>
                    <button type="button" @click="submitQuickAdd()" :disabled="!quickAdd.name.trim() || quickAdd.submitting"
                        class="px-3 py-1.5 rounded text-sm bg-amber-800 text-white disabled:opacity-40">Simpan</button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
