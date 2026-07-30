<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\StockDistribution;
use App\Models\StockItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockDistributionController extends Controller
{
    public function index(Store $store)
    {
        $distributions = $store->stockDistributions()
            ->with(['stockItem.unit', 'distributor'])
            ->latest('distribution_date')
            ->paginate(20);

        $stockItems = $store->stockItems()->with('unit')->orderBy('name')->get();

        return view('owner.stock-distributions.index', compact('store', 'distributions', 'stockItems'));
    }

    public function create(Store $store)
    {
        $stockItems = $store->stockItems()->with('unit')->orderBy('name')->get();

        return view('owner.stock-distributions.create', compact('store', 'stockItems'));
    }

    
    public function store(Request $request, Store $store)
    {
        $validated = $request->validate([
            'stock_item_id' => ['required', 'exists:stock_items,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'distribution_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated, $store, $request) {
            
            StockDistribution::create([
                'store_id' => $store->id,
                'stock_item_id' => $validated['stock_item_id'],
                'quantity' => $validated['quantity'],
                'distributed_by' => $request->user()->id,
                'distribution_date' => $validated['distribution_date'],
                'note' => $validated['note'] ?? null,
            ]);

            
            StockMovement::create([
                'store_id' => $store->id,
                'stock_item_id' => $validated['stock_item_id'],
                'type' => 'distribution',
                'quantity' => $validated['quantity'],
                'note' => 'Distribusi dari Owner: ' . ($validated['note'] ?? '-'),
                'created_by' => $request->user()->id,
            ]);

            StockItem::where('id', $validated['stock_item_id'])
                ->where('store_id', $store->id)
                ->increment('quantity', $validated['quantity']);
        });

        return redirect()
            ->route('owner.stores.stock-distributions.index', $store)
            ->with('success', 'Stok berhasil didistribusikan ke toko.');
    }
}