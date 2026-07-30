<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Store;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Support\StoreCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function index(Request $request, Store $store)
    {
        $channel = $request->input('channel'); 

        $transactions = $store->transactions()
            ->with(['customer', 'staff', 'items.product'])
            ->when($channel, fn ($q) => $q->where('channel', $channel))
            ->latest()
            ->paginate(20);

        return view('store.transactions.index', compact('store', 'transactions', 'channel'));
    }

    
    public function create(Request $request, Store $store)
    {
        $channel = $request->input('channel', 'offline'); 
        $today = now()->toDateString();

        $categories = StoreCache::categories($store->id);

        $activePromotions = collect(StoreCache::activePromotions($store->id))
            ->filter(fn (array $promotion) => $promotion['is_active']
                && $promotion['start_date'] <= $today
                && $promotion['end_date'] >= $today
                && in_array($promotion['channel'], [$channel, 'both']));

        
        
        $stockQuantities = StockItem::where('store_id', $store->id)->pluck('quantity', 'id');

        $productsForJs = collect(StoreCache::products($store->id))->map(fn (array $product) => [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'category_id' => $product['category_id'],
            'recipes' => collect($product['recipes'])->map(fn (array $recipe) => [
                'stock_item_id' => $recipe['stock_item_id'],
                'quantity_needed' => $recipe['quantity_needed'],
                'available_qty' => (float) ($stockQuantities[$recipe['stock_item_id']] ?? 0),
            ])->values(),
        ])->values();

        $customers = $store->customers()->orderBy('name')->get(['id', 'name', 'phone']);

        $promotionsForJs = $activePromotions->map(fn (array $promotion) => [
            'id' => $promotion['id'],
            'name' => $promotion['name'],
            'type' => $promotion['type'],
            'value' => $promotion['value'],
        ])->values();

        return view('store.transactions.create', compact(
            'store', 'channel', 'categories', 'activePromotions',
            'productsForJs', 'customers', 'promotionsForJs'
        ));
    }

    /**
     * "Input" transaksi - dipakai untuk Offline maupun Online (channel dibedakan dari request).
     * Payload contoh:
     * {
     *   "channel": "offline",
     *   "customer_id": null,
     *   "promotion_id": null,
     *   "payment_method": "cash",
     *   "items": [{ "product_id": 1, "quantity": 2 }, ...]
     * }
     */
    public function store(Request $request, Store $store)
    {
        $validated = $request->validate([
            'channel' => ['required', 'in:offline,online'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'promotion_id' => ['nullable', 'exists:promotions,id'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $transaction = DB::transaction(function () use ($validated, $store, $request) {
            $products = Product::whereIn('id', collect($validated['items'])->pluck('product_id'))
                ->where('store_id', $store->id)
                ->with('recipes')
                ->get()
                ->keyBy('id');

            $subtotal = 0;
            $itemsToCreate = [];

            
            
            $stockToDeduct = []; 

            foreach ($validated['items'] as $item) {
                $product = $products->get($item['product_id']);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => 'Produk tidak ditemukan di toko ini.',
                    ]);
                }

                $lineSubtotal = $product->price * $item['quantity'];
                $subtotal += $lineSubtotal;

                $itemsToCreate[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $lineSubtotal,
                ];

                
                foreach ($product->recipes as $recipe) {
                    $needed = $recipe->quantity_needed * $item['quantity'];
                    $stockToDeduct[$recipe->stock_item_id] = ($stockToDeduct[$recipe->stock_item_id] ?? 0) + $needed;
                }
            }

            
            if (! empty($stockToDeduct)) {
                
                $stockItems = StockItem::whereIn('id', array_keys($stockToDeduct))
                    ->where('store_id', $store->id)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($stockToDeduct as $stockItemId => $qtyNeeded) {
                    $stockItem = $stockItems->get($stockItemId);

                    if (! $stockItem) {
                        continue; 
                    }

                    if ($stockItem->quantity < $qtyNeeded) {
                        throw ValidationException::withMessages([
                            'items' => "Stok \"{$stockItem->name}\" tidak cukup. Sisa {$stockItem->quantity} {$stockItem->unit->symbol}, butuh {$qtyNeeded}.",
                        ]);
                    }
                }
            }

            
            $discount = 0;
            $promotion = null;
            if (! empty($validated['promotion_id'])) {
                $promotion = Promotion::where('id', $validated['promotion_id'])
                    ->where('store_id', $store->id)
                    ->first();

                $promoAppliesToChannel = $promotion
                    && ($promotion->channel === 'both' || $promotion->channel === $validated['channel']);

                if ($promotion && $promotion->isValidNow() && $promoAppliesToChannel) {
                    $discount = $promotion->calculateDiscount($subtotal);
                }
            }

            $total = max($subtotal - $discount, 0);

            $transaction = Transaction::create([
                'invoice_number' => Transaction::generateInvoiceNumber($store->id),
                'store_id' => $store->id,
                'channel' => $validated['channel'],
                'customer_id' => $validated['customer_id'] ?? null,
                'staff_id' => $request->user()->id,
                'promotion_id' => $promotion?->id,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'payment_method' => $validated['payment_method'] ?? null,
                'status' => 'completed', 
            ]);

            foreach ($itemsToCreate as $itemData) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    ...$itemData,
                ]);
            }

            
            if (! empty($stockToDeduct)) {
                foreach ($stockToDeduct as $stockItemId => $qtyNeeded) {
                    $stockItem = $stockItems->get($stockItemId);

                    if (! $stockItem) {
                        continue;
                    }

                    $stockItem->decrement('quantity', $qtyNeeded);

                    StockMovement::create([
                        'store_id' => $store->id,
                        'stock_item_id' => $stockItemId,
                        'type' => 'out',
                        'quantity' => $qtyNeeded,
                        'note' => "Terpakai untuk penjualan {$transaction->invoice_number}",
                        'created_by' => $request->user()->id,
                    ]);
                }
            }

            return $transaction;
        });

        if ($request->wantsJson()) {
            return response()->json([
                'redirect' => route('stores.transactions.show', [$store, $transaction]),
            ]);
        }

        return redirect()
            ->route('stores.transactions.show', [$store, $transaction])
            ->with('success', "Transaksi {$transaction->invoice_number} berhasil disimpan.");
    }

    public function show(Store $store, Transaction $transaction)
    {
        abort_unless($transaction->store_id === $store->id, 404);

        $transaction->load(['items.product', 'customer', 'staff', 'promotion']);

        return view('store.transactions.show', compact('store', 'transaction'));
    }

    public function cancel(Store $store, Transaction $transaction)
    {
        abort_unless($transaction->store_id === $store->id, 404);

        $transaction->update(['status' => 'cancelled']);

        return back()->with('success', 'Transaksi dibatalkan.');
    }
}
