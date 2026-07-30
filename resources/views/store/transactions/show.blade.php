@extends('layouts.app')
@section('title', 'Detail Transaksi')
@section('content')

<div class="max-w-md mx-auto bg-white rounded-lg shadow p-6">
    <div class="text-center mb-4">
        <h1 class="font-bold text-lg">{{ $store->name }}</h1>
        <p class="text-xs text-gray-400">{{ $transaction->invoice_number }}</p>
        <p class="text-xs text-gray-400">{{ $transaction->created_at->format('d M Y, H:i') }}</p>
        <span @class([
            'inline-block mt-1 text-xs px-2 py-0.5 rounded capitalize',
            'bg-green-100 text-green-700' => $transaction->status === 'completed',
            'bg-yellow-100 text-yellow-700' => $transaction->status === 'pending',
            'bg-red-100 text-red-700' => $transaction->status === 'cancelled',
        ])>{{ $transaction->channel }} - {{ $transaction->status }}</span>
    </div>

    <div class="border-t border-b py-3 space-y-2 text-sm">
        @foreach($transaction->items as $item)
            <div class="flex justify-between">
                <span>{{ $item->product->name }} x{{ $item->quantity }}</span>
                <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
            </div>
        @endforeach
    </div>

    <div class="pt-3 space-y-1 text-sm">
        <div class="flex justify-between"><span>Subtotal</span><span>Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span></div>
        @if($transaction->discount > 0)
            <div class="flex justify-between text-green-700">
                <span>Diskon ({{ $transaction->promotion->name ?? '-' }})</span>
                <span>- Rp {{ number_format($transaction->discount, 0, ',', '.') }}</span>
            </div>
        @endif
        <div class="flex justify-between font-bold text-base pt-1 border-t">
            <span>Total</span><span>Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
        </div>
        <p class="text-xs text-gray-400 pt-1">Pembayaran: {{ strtoupper($transaction->payment_method ?? '-') }}</p>
        <p class="text-xs text-gray-400">Kasir: {{ $transaction->staff->name ?? '-' }}</p>
        @if($transaction->customer)
            <p class="text-xs text-gray-400">Customer: {{ $transaction->customer->name }}</p>
        @endif
    </div>

    <button type="button" onclick="window.print()" class="no-print w-full mt-4 text-sm border border-amber-300 text-amber-800 rounded py-2 hover:bg-amber-50">
        🖨️ Cetak Struk
    </button>

    @if($transaction->status === 'completed')
        <form method="POST" action="{{ route('stores.transactions.cancel', [$store, $transaction]) }}"
            onsubmit="return confirm('Batalkan transaksi ini?')" class="no-print mt-2">
            @csrf
            <button class="w-full text-red-600 text-sm border border-red-300 rounded py-2 hover:bg-red-50">
                Batalkan Transaksi
            </button>
        </form>
    @endif

    <a href="{{ route('stores.transactions.index', $store) }}" class="no-print block text-center text-sm text-amber-800 mt-3 hover:underline">
        ← Kembali ke Riwayat
    </a>
</div>

@endsection
