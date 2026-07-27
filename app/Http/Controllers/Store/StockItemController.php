<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockItemController extends Controller
{
    // "Management Stok" - list bahan baku toko ini
    public function index(Store $store)
    {
        $stockItems = $store->stockItems()->with('unit')->orderBy('name')->get();

        // Satuan yang bisa dipilih: default (global) + custom milik toko ini
        $availableUnits = Unit::availableFor($store->id)->orderBy('name')->get();

        return view('store.stock-items.index', compact('store', 'stockItems', 'availableUnits'));
    }

    // "Input Stok" - bikin master bahan baku baru
    public function store(Request $request, Store $store)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'unit_id' => ['required', 'exists:units,id'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'minimum_stock' => ['required', 'numeric', 'min:0'],
        ]);

        // Pastikan unit yang dipilih memang boleh dipakai toko ini (default atau custom miliknya)
        $unit = Unit::availableFor($store->id)->findOrFail($validated['unit_id']);

        DB::transaction(function () use ($validated, $store, $request) {
            $stockItem = $store->stockItems()->create($validated);

            if ($validated['quantity'] > 0) {
                StockMovement::create([
                    'store_id' => $store->id,
                    'stock_item_id' => $stockItem->id,
                    'type' => 'in',
                    'quantity' => $validated['quantity'],
                    'note' => 'Stok awal',
                    'created_by' => $request->user()->id,
                ]);
            }
        });

        return back()->with('success', 'Item stok berhasil ditambahkan.');
    }

    // "Input Pengeluaran/pemakaian stok" - kurangi stok manual (misal terpakai produksi, rusak, dll)
    public function adjustStock(Request $request, Store $store, StockItem $stockItem)
    {
        abort_unless($stockItem->store_id === $store->id, 404);

        $validated = $request->validate([
            'type' => ['required', 'in:in,out,adjustment'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated, $store, $stockItem, $request) {
            StockMovement::create([
                'store_id' => $store->id,
                'stock_item_id' => $stockItem->id,
                'type' => $validated['type'],
                'quantity' => $validated['quantity'],
                'note' => $validated['note'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            if ($validated['type'] === 'in') {
                $stockItem->increment('quantity', $validated['quantity']);
            } else {
                // out & adjustment (pengurangan) mengurangi stok
                $stockItem->decrement('quantity', $validated['quantity']);
            }
        });

        return back()->with('success', 'Stok berhasil diupdate.');
    }

    public function update(Request $request, Store $store, StockItem $stockItem)
    {
        abort_unless($stockItem->store_id === $store->id, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'unit_id' => ['required', 'exists:units,id'],
            'minimum_stock' => ['required', 'numeric', 'min:0'],
        ]);

        Unit::availableFor($store->id)->findOrFail($validated['unit_id']);

        $stockItem->update($validated);

        return back()->with('success', 'Item stok berhasil diupdate.');
    }

    public function destroy(Store $store, StockItem $stockItem)
    {
        abort_unless($stockItem->store_id === $store->id, 404);

        $stockItem->delete();

        return back()->with('success', 'Item stok berhasil dihapus.');
    }
}
