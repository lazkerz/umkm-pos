@extends('layouts.app')
@section('title', 'Kasir - ' . $store->name)
@section('content')

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
        <div class="grid grid-cols-3 gap-3">
            @foreach($products as $p)
                <button type="button"
                    onclick="addToCart({{ $p->id }}, '{{ addslashes($p->name) }}', {{ $p->price }})"
                    class="bg-white rounded-lg shadow p-4 text-left hover:ring-2 hover:ring-amber-500">
                    <p class="font-medium text-sm">{{ $p->name }}</p>
                    <p class="text-amber-800 font-bold text-sm mt-1">Rp {{ number_format($p->price, 0, ',', '.') }}</p>
                </button>
            @endforeach
        </div>
    </div>

    {{-- CART / INPUT --}}
    <div class="bg-white rounded-lg shadow p-5 h-fit sticky top-6">
        <h2 class="font-bold mb-3">🧾 Pesanan</h2>

        <div id="cart-items" class="space-y-2 mb-4 text-sm max-h-64 overflow-y-auto">
            <p id="empty-cart" class="text-gray-400 text-center py-4">Belum ada item dipilih</p>
        </div>

        <div class="border-t pt-3 space-y-1 text-sm mb-3">
            <div class="flex justify-between"><span>Subtotal</span><span id="subtotal-display">Rp 0</span></div>
            @if($activePromotions->count())
                <div class="flex justify-between items-center">
                    <span>Promo</span>
                    <select id="promotion-select" onchange="renderCart()" class="border rounded px-2 py-1 text-xs">
                        <option value="">Tanpa Promo</option>
                        @foreach($activePromotions as $promo)
                            <option value="{{ $promo->id }}"
                                data-type="{{ $promo->type }}"
                                data-value="{{ $promo->value }}">
                                {{ $promo->name }} ({{ $promo->type === 'percentage' ? $promo->value.'%' : 'Rp '.number_format($promo->value,0,',','.') }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="flex justify-between font-bold text-base pt-1"><span>Total</span><span id="total-display">Rp 0</span></div>
        </div>

        <form method="POST" action="{{ route('stores.transactions.store', $store) }}" id="checkout-form">
            @csrf
            <input type="hidden" name="channel" value="{{ $channel }}">
            <div id="hidden-items"></div>

            <div class="mb-3">
                <label class="block text-xs font-medium mb-1">Metode Pembayaran</label>
                <select name="payment_method" class="w-full border rounded px-3 py-1.5 text-sm">
                    <option value="cash">Cash</option>
                    <option value="qris">QRIS</option>
                    <option value="transfer">Transfer</option>
                </select>
            </div>

            <button type="submit" id="submit-btn" disabled
                class="w-full bg-amber-800 text-white py-2 rounded text-sm hover:bg-amber-900 disabled:opacity-40">
                Proses Transaksi
            </button>
        </form>
    </div>
</div>

<script>
let cart = {}; // { product_id: { name, price, qty } }

function addToCart(id, name, price) {
    if (!cart[id]) cart[id] = { name, price, qty: 0 };
    cart[id].qty += 1;
    renderCart();
}

function removeFromCart(id) {
    if (!cart[id]) return;
    cart[id].qty -= 1;
    if (cart[id].qty <= 0) delete cart[id];
    renderCart();
}

function renderCart() {
    const container = document.getElementById('cart-items');
    const emptyMsg = document.getElementById('empty-cart');
    const hiddenItems = document.getElementById('hidden-items');
    const ids = Object.keys(cart);

    container.innerHTML = '';
    hiddenItems.innerHTML = '';

    if (ids.length === 0) {
        container.appendChild(emptyMsg);
        document.getElementById('submit-btn').disabled = true;
    } else {
        document.getElementById('submit-btn').disabled = false;
    }

    let subtotal = 0;

    ids.forEach((id, idx) => {
        const item = cart[id];
        const lineTotal = item.price * item.qty;
        subtotal += lineTotal;

        const row = document.createElement('div');
        row.className = 'flex justify-between items-center';
        row.innerHTML = `
            <div class="flex-1">
                <p class="font-medium">${item.name}</p>
                <p class="text-xs text-gray-400">Rp ${item.price.toLocaleString('id-ID')} x ${item.qty}</p>
            </div>
            <div class="flex items-center gap-1">
                <button type="button" onclick="removeFromCart(${id})" class="w-6 h-6 bg-gray-200 rounded">-</button>
                <span class="w-6 text-center">${item.qty}</span>
                <button type="button" onclick="addToCart(${id}, '${item.name}', ${item.price})" class="w-6 h-6 bg-gray-200 rounded">+</button>
            </div>
        `;
        container.appendChild(row);

        hiddenItems.innerHTML += `
            <input type="hidden" name="items[${idx}][product_id]" value="${id}">
            <input type="hidden" name="items[${idx}][quantity]" value="${item.qty}">
        `;
    });

    // Hitung diskon dari promo yang dipilih (cuma untuk PREVIEW, kalkulasi final tetap di server)
    let discount = 0;
    const promoSelect = document.getElementById('promotion-select');
    if (promoSelect && promoSelect.value) {
        const selected = promoSelect.options[promoSelect.selectedIndex];
        const type = selected.dataset.type;
        const value = parseFloat(selected.dataset.value);
        discount = type === 'percentage' ? subtotal * (value / 100) : Math.min(value, subtotal);

        hiddenItems.innerHTML += `<input type="hidden" name="promotion_id" value="${promoSelect.value}">`;
    }

    const total = Math.max(subtotal - discount, 0);

    document.getElementById('subtotal-display').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
    document.getElementById('total-display').textContent = 'Rp ' + total.toLocaleString('id-ID');
}
</script>

@endsection
